<?php

declare(strict_types=1);

namespace Comfino\Api;

use Comfino\Api\Exception\AccessDenied;
use Comfino\Api\Exception\AuthorizationError;
use Comfino\Api\Exception\Conflict;
use Comfino\Api\Exception\Forbidden;
use Comfino\Api\Exception\MethodNotAllowed;
use Comfino\Api\Exception\NonJsonResponse;
use Comfino\Api\Exception\NotFound;
use Comfino\Api\Exception\RequestValidationError;
use Comfino\Api\Exception\ResponseValidationError;
use Comfino\Api\Exception\ServiceUnavailable;
use Comfino\Api\Exception\TooManyRequests;
use Psr\Http\Message\ResponseInterface;

abstract class Response
{
    /** @var Request Comfino API client request object associated with this response. */
    protected Request $request;
    /** @var ResponseInterface|null PSR-7 compatible HTTP response object. */
    protected ?ResponseInterface $response;
    /** @var SerializerInterface Serializer/deserializer object for requests and responses body. */
    protected SerializerInterface $serializer;
    /** @var \Throwable|null Exception object in case of validation or communication error. */
    protected ?\Throwable $exception;
    /** @var string[] Extracted HTTP response headers. */
    protected array $headers = [];

    /**
     * Returns response HTTP headers as associative array ['headerName' => 'headerValue'].
     *
     * @return string[]
     */
    final public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Checks if specified response HTTP header exists (case-insensitive).
     *
     * @param string $headerName
     *
     * @return bool
     */
    final public function hasHeader(string $headerName): bool
    {
        if (isset($this->headers[$headerName])) {
            return true;
        }

        foreach ($this->headers as $responseHeaderName => $headerValue) {
            if (strcasecmp($responseHeaderName, $headerName) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns specified response HTTP header (case-insensitive) or default value if it does not exist.
     *
     * @param string $headerName
     * @param string|null $defaultValue
     *
     * @return string|null
     */
    final public function getHeader(string $headerName, ?string $defaultValue = null): ?string
    {
        if (isset($this->headers[$headerName])) {
            return $this->headers[$headerName];
        }

        foreach ($this->headers as $responseHeaderName => $headerValue) {
            if (strcasecmp($responseHeaderName, $headerName) === 0) {
                return $headerValue;
            }
        }

        return $defaultValue;
    }

    /**
     * Extracts API response data from input PSR-7 compatible HTTP response object.
     *
     * @return Response Comfino API client response object.
     *
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     */
    final protected function initFromPsrResponse(): self
    {
        // Handle null response (network errors, connection failures, etc.).
        if ($this->response === null) {
            return $this;
        }

        $requestBody = ($this->request->getRequestBody() ?? '');

        $this->response->getBody()->rewind();
        $responseBody = $this->response->getBody()->getContents();

        $this->headers = [];

        foreach ($this->response->getHeaders() as $headerName => $headerValues) {
            $this->headers[$headerName] = end($headerValues);
        }

        if ($this->exception !== null) {
            // Exception already thrown - return without errors processing and exceptions throwing.
            return $this;
        }

        $statusCode = $this->response->getStatusCode();

        /* The HTTP status decides the exception class; the body only decorates it. A 5xx must never be reclassified as
           a client-side validation error just because its body is malformed JSON or an HTML gateway/proxy error page
           sent with a misleading `application/json` Content-Type. The raw body is redacted and length-capped here because
           gateway error pages can carry PII-adjacent diagnostic dumps and can be arbitrarily large. */
        if ($statusCode >= 500) {
            throw new ServiceUnavailable(
                "Comfino API service is unavailable: {$this->response->getReasonPhrase()} [{$statusCode}]",
                $statusCode,
                null,
                $this->request->getRequestUri(),
                $requestBody,
                SensitiveDataRedactor::redactPayload($responseBody)
            );
        }

        $contentType = $this->response->hasHeader('Content-Type') ? $this->response->getHeader('Content-Type')[0] : '';
        $nonJson = false;

        try {
            $deserializedResponseBody = $this->deserializeResponseBody($responseBody, $this->serializer);
        } catch (ResponseValidationError $e) {
            $deserializedResponseBody = null;
            $nonJson = true;
        }

        // 4xx status codes
        if ($statusCode >= 400) {
            switch ($statusCode) {
                case 400:
                    throw new RequestValidationError(
                        $this->getErrorMessage(
                            $statusCode,
                            $deserializedResponseBody,
                            "Invalid request data: {$this->response->getReasonPhrase()} [{$statusCode}]"
                        ),
                        $statusCode,
                        null,
                        $this->request->getRequestUri(),
                        $requestBody,
                        $responseBody,
                        $deserializedResponseBody,
                        $this->response
                    );

                case 401:
                    throw new AuthorizationError(
                        $this->getErrorMessage(
                            $statusCode,
                            $deserializedResponseBody,
                            "Invalid credentials: {$this->response->getReasonPhrase()} [{$statusCode}]",
                        ),
                        $statusCode,
                        null,
                        $this->request->getRequestUri(),
                        $requestBody
                    );

                case 403:
                    throw new Forbidden(
                        $this->getErrorMessage(
                            $statusCode,
                            $deserializedResponseBody,
                            "Access denied: {$this->response->getReasonPhrase()} [{$statusCode}]"
                        ),
                        $statusCode,
                        null,
                        $this->request->getRequestUri(),
                        $requestBody,
                        $responseBody
                    );

                case 404:
                    throw (new NotFound(
                        $this->getErrorMessage(
                            $statusCode,
                            $deserializedResponseBody,
                            "Entity not found: {$this->response->getReasonPhrase()} [{$statusCode}]"
                        ),
                        $statusCode,
                        null,
                        $this->request->getRequestUri(),
                        $requestBody,
                        $responseBody
                    ))->setIdempotentFailure($this->request->isIdempotentFailure($statusCode));

                case 405:
                    throw new MethodNotAllowed(
                        $this->getErrorMessage(
                            $statusCode,
                            $deserializedResponseBody,
                            "Method not allowed: {$this->response->getReasonPhrase()} [{$statusCode}]"
                        ),
                        $statusCode,
                        null,
                        $this->request->getRequestUri(),
                        $requestBody,
                        $responseBody
                    );

                case 409:
                    throw (new Conflict(
                        $this->getErrorMessage(
                            $statusCode,
                            $deserializedResponseBody,
                            "Entity already exists: {$this->response->getReasonPhrase()} [{$statusCode}]"
                        ),
                        $statusCode,
                        null,
                        $this->request->getRequestUri(),
                        $requestBody,
                        $responseBody
                    ))->setIdempotentFailure($this->request->isIdempotentFailure($statusCode));

                case 429:
                    throw new TooManyRequests(
                        $this->getErrorMessage(
                            $statusCode,
                            $deserializedResponseBody,
                            "Too many requests: {$this->response->getReasonPhrase()} [{$statusCode}]"
                        ),
                        $statusCode,
                        null,
                        $this->request->getRequestUri(),
                        $requestBody,
                        $responseBody,
                        $deserializedResponseBody,
                        $this->response,
                        $this->parseRetryAfterSeconds()
                    );

                default:
                    throw new RequestValidationError(
                        "Invalid request data: {$this->response->getReasonPhrase()} [{$statusCode}]",
                        $statusCode,
                        null,
                        $this->request->getRequestUri(),
                        $requestBody,
                        $responseBody,
                        $deserializedResponseBody,
                        $this->response
                    );
            }
        }

        if (($errorMessage = $this->getErrorMessage($statusCode, $deserializedResponseBody)) !== null) {
            throw new RequestValidationError(
                $errorMessage,
                $statusCode,
                null,
                $this->request->getRequestUri(),
                $requestBody,
                $responseBody,
                $deserializedResponseBody,
                $this->response
            );
        }

        if ($nonJson) {
            throw new NonJsonResponse(
                "Invalid response data: non-JSON response body received [{$statusCode}]",
                $statusCode,
                null,
                $this->request->getRequestUri(),
                $requestBody,
                $responseBody,
                $contentType
            );
        }

        try {
            $this->processResponseBody($deserializedResponseBody);
        } catch (ResponseValidationError $e) {
            $e->setUrl($this->request->getRequestUri());
            $e->setRequestBody($requestBody);
            $e->setResponseBody($responseBody);

            throw $e;
        }

        return $this;
    }

    /** Parses only the seconds form of `Retry-After`; the HTTP-date form is ignored (F1). */
    private function parseRetryAfterSeconds(): ?int
    {
        $retryAfter = $this->getHeader('Retry-After');

        return $retryAfter !== null && ctype_digit($retryAfter) ? (int) $retryAfter : null;
    }

    /**
     * Fills response object properties with data from deserialized API response array.
     *
     * @throws ResponseValidationError
     */
    abstract protected function processResponseBody(array|string|bool|null $deserializedResponseBody): void;

    /**
     * @throws ResponseValidationError
     */
    private function deserializeResponseBody(string $responseBody, SerializerInterface $serializer): array|string|bool|null
    {
        return !empty($responseBody) ? $serializer->unserialize($responseBody) : null;
    }

    private function getErrorMessage(int $statusCode, array|string|bool|null $deserializedResponseBody, ?string $defaultMessage = null): ?string
    {
        if (!is_array($deserializedResponseBody)) {
            return $defaultMessage;
        }

        $errorMessages = [];

        if (isset($deserializedResponseBody['errors'])) {
            $errorMessages = array_map(
                static fn (string $errorFieldName, string $errorMessage) => "$errorFieldName: $errorMessage",
                array_keys($deserializedResponseBody['errors']),
                array_values($deserializedResponseBody['errors'])
            );
        } elseif (isset($deserializedResponseBody['message'])) {
            $errorMessages = [$deserializedResponseBody['message']];
        } elseif ($statusCode >= 400) {
            foreach ($deserializedResponseBody as $errorFieldName => $errorMessage) {
                $errorMessages[] = "$errorFieldName: $errorMessage";
            }
        }

        return count($errorMessages) ? implode("\n", $errorMessages) : $defaultMessage;
    }
}
