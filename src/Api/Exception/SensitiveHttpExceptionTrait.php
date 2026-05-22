<?php

declare(strict_types=1);

namespace Comfino\Api\Exception;

use Comfino\Api\SensitiveDataRedactor;

/**
 * Adds PII-safe defaults to HTTP-error exceptions that retain raw request and
 * response bodies for debugging.
 *
 * Why this is needed: the request body persisted on every HTTP exception
 * contains customer firstName, lastName, e-mail, phone, taxId, IP and postal
 * address. Legacy shop platforms (WooCommerce, PrestaShop 1.6, Magento 1) and
 * the third-party error handlers they ship with (Whoops, Sentry default,
 * monolog with print_r formatter) routinely serialize whole exception objects
 * into error_log, on-screen error pages, or remote monitoring sinks. Without
 * __debugInfo() those dumps leak PII in plain text and create a GDPR breach.
 *
 * The raw getters are preserved so that callers performing controlled,
 * authorised debugging can still inspect the original payload - the trait
 * only changes what gets emitted by var_dump / print_r / Sentry's default
 * object serializer.
 */
trait SensitiveHttpExceptionTrait
{
    /**
     * Returns the request body with sensitive fields (e-mail, phone, taxId,
     * address, credentials) masked. Safe to log or forward to error tracking.
     */
    public function getRedactedRequestBody(): string
    {
        return SensitiveDataRedactor::redactJsonString($this->getRequestBody());
    }

    /**
     * Returns the response body with sensitive fields masked. Safe to log or
     * forward to error tracking. Returns an empty string when the exception
     * does not retain a response body (AuthorizationError).
     */
    public function getRedactedResponseBody(): string
    {
        return SensitiveDataRedactor::redactJsonString($this->getResponseBody());
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'url' => $this->getUrl(),
            'statusCode' => $this->getStatusCode(),
            'requestBody' => $this->getRedactedRequestBody(),
            'responseBody' => $this->getRedactedResponseBody(),
        ];
    }
}