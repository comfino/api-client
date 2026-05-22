<?php

declare(strict_types=1);

namespace Comfino\Api\Serializer;

use Comfino\Api\Exception\RequestValidationError;
use Comfino\Api\Exception\ResponseValidationError;
use Comfino\Api\SerializerInterface;

class Json implements SerializerInterface
{
    public function serialize(mixed $requestData): string
    {
        try {
            $serializedRequestBody = json_encode($requestData, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
        } catch (\JsonException $e) {
            throw new RequestValidationError("Invalid request data: {$e->getMessage()}", 0, $e);
        }

        /* Defensive fallback for runtimes where JSON_THROW_ON_ERROR is unavailable (e.g., PHP 7.1 after Rector
           transpilation strips the flag). On those runtimes json_encode() returns false on failure instead of throwing,
           which would otherwise let a malformed payload be hashed/sent silently. */
        if ($serializedRequestBody === false) {
            throw new RequestValidationError('Invalid request data: ' . json_last_error_msg(), 0);
        }

        return $serializedRequestBody;
    }

    public function unserialize(string $responseBody): mixed
    {
        try {
            $deserializedResponseBody = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new ResponseValidationError("Invalid response data: {$e->getMessage()}", 0, $e, responseBody: $responseBody);
        }

        if ($deserializedResponseBody === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new ResponseValidationError(
                'Invalid response data: ' . json_last_error_msg(),
                0,
                null,
                responseBody: $responseBody
            );
        }

        return $deserializedResponseBody;
    }
}