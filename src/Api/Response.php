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
    /** @var SerializerInterface */
    protected SerializerInterface $serializer;

    /**
     * Extracts API response data from input PSR-7 compatible HTTP response object.
     *
     * @param ResponseInterface $response
     * @return Response
     * @throws RequestValidationError
     * @throws ResponseValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     */
    final public function initFromPsrResponse(ResponseInterface $response): self
    {
        $responseBody = $response->getBody();
        $responseBody->rewind();

        $deserializedResponseBody = $this->deserializeResponseBody($responseBody->getContents());

        if ($response->getStatusCode() >= 500) {
            throw new ServiceUnavailable(
                "Comfino API service is unavailable: {$response->getReasonPhrase()} [{$response->getStatusCode()}]"
            );
        }

        if ($response->getStatusCode() >= 400) {
            throw match ($response->getStatusCode()) {
                400 => new RequestValidationError(
                    $this->getErrorMessage(
                        $deserializedResponseBody,
                        "Invalid request data: {$response->getReasonPhrase()} [{$response->getStatusCode()}]"
                    )
                ),
                401 => new AuthorizationError(
                    $this->getErrorMessage(
                        $deserializedResponseBody,
                        "Invalid credentials: {$response->getReasonPhrase()} [{$response->getStatusCode()}]"
                    )
                ),
                402, 403, 404, 405 => new AccessDenied(
                    $this->getErrorMessage(
                        $deserializedResponseBody,
                        "Access denied: {$response->getReasonPhrase()} [{$response->getStatusCode()}]"
                    )
                ),
                default => new RequestValidationError(
                    "Invalid request data: {$response->getReasonPhrase()} [{$response->getStatusCode()}]"
                ),
            };
        }

        if (($errorMessage = $this->getErrorMessage($deserializedResponseBody)) !== null) {
            throw new RequestValidationError($errorMessage);
        }

        $this->processResponseBody($deserializedResponseBody);

        return $this;
    }

    /**
     * Fills response object properties with data from deserialized API response array.
     */
    abstract protected function processResponseBody(array|string|bool|null $deserializedResponseBody): void;

    /**
     * @throws ResponseValidationError
     */
    private function deserializeResponseBody(string $responseBody): array|string|bool|null
    {
        return !empty($responseBody) ? $this->serializer->unserialize($responseBody) : null;
    }

    private function getErrorMessage(array|string|bool|null $deserializedResponseBody, ?string $defaultMessage = null): ?string
    {
        if (!is_array($deserializedResponseBody)) {
            return null;
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
        }

        return count($errorMessages) ? implode("\n", $errorMessages) : $defaultMessage;
    }
}
