<?php

declare(strict_types=1);

namespace Comfino\Api\Exception;

use Psr\Http\Message\ResponseInterface;

/**
 * Thrown for HTTP 429 responses. Unlike a generic RequestValidationError, this is a transient failure: the caller
 * (or the outbound request queue) should retry after the delay carried in the `Retry-After` header, when present.
 */
class TooManyRequests extends RequestValidationError
{
    private ?int $retryAfterSeconds;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        string $url = '',
        string $requestBody = '',
        string $responseBody = '',
        $deserializedResponseBody = null,
        ?ResponseInterface $response = null,
        ?int $retryAfterSeconds = null
    ) {
        parent::__construct($message, $code, $previous, $url, $requestBody, $responseBody, $deserializedResponseBody, $response);

        $this->retryAfterSeconds = $retryAfterSeconds;
    }

    /** Seconds to wait before retrying, parsed from the `Retry-After` header (seconds form only), or null. */
    public function getRetryAfterSeconds(): ?int
    {
        return $this->retryAfterSeconds;
    }

    public function getStatusCode(): int
    {
        return 429;
    }
}
