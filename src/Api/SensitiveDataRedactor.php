<?php

declare(strict_types=1);

namespace Comfino\Api;

/**
 * Masks personally-identifiable information (PII) and credentials in serialized request/response payloads so that
 * exception dumps (var_dump, print_r, Whoops, Sentry default scrubbers, error_log of "$e") do not leak GDPR-sensitive
 * fields such as customer e-mail, phone number, tax ID, IP, or postal address.
 *
 * The redactor is intentionally conservative: when the payload is not valid JSON it falls back to a length-capped
 * marker rather than attempting partial parsing, which would risk regex-based data leaks.
 */
final class SensitiveDataRedactor
{
    /**
     * Field-name fragments that trigger masking. Matched case-insensitively against
     * each JSON object key. Listed individually (not as a regex) so that the list
     * is auditable and easy to extend.
     */
    private const SENSITIVE_KEY_FRAGMENTS = [
        'email',
        'phone',
        'taxid',
        'tax_id',
        'firstname',
        'first_name',
        'lastname',
        'last_name',
        'street',
        'postalcode',
        'postal_code',
        'zipcode',
        'zip_code',
        'housenumber',
        'house_number',
        'flatnumber',
        'flat_number',
        'buildingnumber',
        'building_number',
        'city',
        'ip',
        'ipaddress',
        'ip_address',
        'apikey',
        'api_key',
        'authorization',
        'password',
        'secret',
        'token',
        'credential',
        'pesel',
        'nip',
        'regon',
    ];

    private const REDACTED = '***REDACTED***';

    public static function redactJsonString(string $payload): string
    {
        if ($payload === '') {
            return '';
        }

        $decoded = json_decode($payload, true);

        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            /*
             * Not valid JSON (e.g. an HTML error page from a misrouted response).
             * Returning the raw body would defeat the purpose of redaction, so we
             * emit a short length-only marker instead.
             */
            return sprintf('***NON-JSON-PAYLOAD (%d bytes)***', strlen($payload));
        }

        $redacted = self::redactStructure($decoded);
        $reencoded = json_encode($redacted, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $reencoded === false ? self::REDACTED : $reencoded;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function redactStructure(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $result = [];

        foreach ($value as $key => $item) {
            if (is_string($key) && self::isSensitiveKey($key)) {
                $result[$key] = $item === null ? null : self::REDACTED;

                continue;
            }

            $result[$key] = is_array($item) ? self::redactStructure($item) : $item;
        }

        return $result;
    }

    private static function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (self::SENSITIVE_KEY_FRAGMENTS as $fragment) {
            if (str_contains($normalized, $fragment)) {
                return true;
            }
        }

        return false;
    }
}
