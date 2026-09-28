<?php

declare(strict_types=1);

namespace Comfino\Api\Exception;

/**
 * Thrown for a 2xx/3xx response whose body cannot be decoded as JSON (e.g. an HTML page returned by a misconfigured
 * proxy or CDN in place of the expected API response). Extends ResponseValidationError so every existing `catch
 * (ResponseValidationError)` block keeps matching.
 */
class NonJsonResponse extends ResponseValidationError
{
    private int $statusCode;
    private string $contentType;

    public function __construct(
        string $message,
        int $statusCode,
        ?\Throwable $previous,
        string $url,
        string $requestBody,
        string $responseBody,
        string $contentType
    ) {
        parent::__construct($message, $statusCode, $previous, $url, $requestBody, $responseBody);

        $this->statusCode = $statusCode;
        $this->contentType = $contentType;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    /** True when the body starts with an HTML/DOCTYPE marker, a common shape for gateway/proxy error pages. */
    public function looksLikeHtml(): bool
    {
        return preg_match('/^<(!DOCTYPE|html)/i', ltrim($this->getResponseBody())) === 1;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
