<?php

namespace Comfino\Api;

use Comfino\Api\Exception\AccessDenied;
use Comfino\Api\Exception\AuthorizationError;
use Comfino\Api\Exception\RequestValidationError;
use Comfino\Api\Exception\ResponseValidationError;
use Comfino\Api\Exception\ServiceUnavailable;
use Psr\Http\Message\ResponseInterface;

abstract class Response
{
    /** @var string[] */
    protected array $headers = [];

    /**
     * Extracts API response data from input PSR-7 compatible HTTP response object.
     *
     * @param Request $request
     * @param ResponseInterface $response
     * @param SerializerInterface $serializer
     * @return Response
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     */
    final public function initFromPsrResponse(Request $request, ResponseInterface $response, SerializerInterface $serializer): self
    {
        $response->getBody()->rewind();
        $responseBody = $response->getBody()->getContents();

        if ($response->hasHeader('Content-Type') && strpos($response->getHeader('Content-Type')[0], 'application/json') !== false) {
            try {
                $deserializedResponseBody = $this->deserializeResponseBody($responseBody, $serializer);
            } catch (ResponseValidationError $e) {
                $e->setUrl($request->getRequestUri());
                $e->setResponseBody($responseBody);

                throw $e;
            }
        } else {
            $deserializedResponseBody = $responseBody;
        }

        $this->headers = [];

        foreach ($response->getHeaders() as $headerName => $headerValues) {
            $this->headers[$headerName] = end($headerValues);
        }

        if ($response->getStatusCode() >= 500) {
            throw new ServiceUnavailable(
                "Comfino API service is unavailable: {$response->getReasonPhrase()} [{$response->getStatusCode()}]",
                0,
                null,
                $request->getRequestUri(),
                '',
                $responseBody
            );
        }

        if ($response->getStatusCode() >= 400) {
            switch ($response->getStatusCode()) {
                case 400:
                    throw new RequestValidationError(
                        $this->getErrorMessage(
                            $response->getStatusCode(),
                            $deserializedResponseBody,
                            "Invalid request data: {$response->getReasonPhrase()} [{$response->getStatusCode()}]"
                        ),
                        0,
                        null,
                        $request->getRequestUri()
                    );

                case 401:
                    throw new AuthorizationError(
                        $this->getErrorMessage(
                            $response->getStatusCode(),
                            $deserializedResponseBody,
                            "Invalid credentials: {$response->getReasonPhrase()} [{$response->getStatusCode()}]",
                        ),
                        0,
                        null,
                        $request->getRequestUri()
                    );

                case 402:
                case 403:
                case 404:
                case 405:
                    throw new AccessDenied(
                        $this->getErrorMessage(
                            $response->getStatusCode(),
                            $deserializedResponseBody,
                            "Access denied: {$response->getReasonPhrase()} [{$response->getStatusCode()}]"
                        ),
                        0,
                        null,
                        $request->getRequestUri()
                    );

                default:
                    throw new RequestValidationError(
                        "Invalid request data: {$response->getReasonPhrase()} [{$response->getStatusCode()}]",
                        0,
                        null,
                        $request->getRequestUri()
                    );
            }
        }

        if (($errorMessage = $this->getErrorMessage($response->getStatusCode(), $deserializedResponseBody)) !== null) {
            throw new RequestValidationError(
                $errorMessage,
                0,
                null,
                $request->getRequestUri()
            );
        }

        try {
            $this->processResponseBody($deserializedResponseBody);
        } catch (ResponseValidationError $e) {
            $e->setUrl($request->getRequestUri());
            $e->setResponseBody($responseBody);

            throw $e;
        }

        return $this;
    }

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
