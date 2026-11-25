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
        'apartmentnumber',
        'apartment_number',
        'buildingnumber',
        'building_number',
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
        'regon',
    ];

    /**
     * Short sensitive words matched as whole words of a key (split on non-alphanumerics and camelCase humps), not as
     * substrings: `city` must not hit `capacity` or `electricity`, nor `nip` hit `snippet`.
     */
    private const SENSITIVE_KEY_WORDS = ['nip', 'city'];

    /** Header lines whose value runs to the end of the line and may hold spaces: `Authorization: Bearer <token>`. */
    private const CREDENTIAL_HEADER_PATTERN = '/\b(authorization|proxy-authorization|cookie|set-cookie)(\s*[:=]\s*)[^\r\n]+/i';
    private const BEARER_PATTERN = '/\bBearer\s+[A-Za-z0-9._~+\/=-]+/i';
    private const JWT_PATTERN = '/\beyJ[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]*/';
    private const SECRET_QUERY_PARAMETER_PATTERN = '/([?&](?:auth|token|access_token|api[_-]?key|logging[_-]?token|signature)=)[^&\s"\']+/i';

    /** Matches the key fragment 'ip' only as a whole token, not as a substring of 'shipping', 'description' etc. */
    private const IP_KEY_PATTERN = '/(^|[_\-.])ip($|[_\-.]|address)/';

    private const EMAIL_PATTERN = '/[a-z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)+/i';
    /**
     * IPv6 candidates: colon-separated hex groups (empty groups allowed for "::"), optionally ending in an embedded IPv4
     * address. A candidate is masked only when filter_var() accepts it as IPv6, so a time ("08:14:03", "12:30:00") or a
     * MAC address ("00:1a:2b:3c:4d:5e") stays readable, while compressed forms ("2001:db8::1", "::1") are masked.
     */
    private const IPV6_CANDIDATE_PATTERN = '/(?<![0-9a-f:])(?:[0-9a-f]{0,4}:){2,7}(?:(?:\d{1,3}\.){3}\d{1,3}|[0-9a-f]{0,4})(?![0-9a-f:])/i';
    private const IPV4_PATTERN = '/\b(?:(?:25[0-5]|2[0-4]\d|1?\d?\d)\.){3}(?:25[0-5]|2[0-4]\d|1?\d?\d)\b/';
    /**
     * A date with an optional time, in the "date" group, ahead of the phone alternative: 9+ digits, optionally starting
     * with '+' and separated by spaces or dashes. At a position where both could match, the date wins and is kept, so
     * "2026-11-21 08:14:03" is not read as the 10-digit phone "2026-11-21 08". Month, day and time ranges are checked by
     * the pattern, so a phone written as "12-34-5678 90" is not a date and is still masked.
     */
    private const PHONE_PATTERN = '/(?<date>(?<![\w.])(?:\d{4}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[12]\d|3[01])|(?:0[1-9]|[12]\d|3[01])[-.](?:0[1-9]|1[0-2])[-.]\d{4})(?:[T ](?:[01]?\d|2[0-3]):[0-5]\d(?::[0-5]\d)?(?:\.\d+)?(?:Z|[+-]\d{2}:?\d{2})?)?(?!\w))|\+?\d(?:[\s-]?\d){8,}/';

    private const REDACTED = '***REDACTED***';

    /**
     * Masks e-mail addresses, IPv4/IPv6 addresses and phone-like digit runs (9+ digits, optional +, spaces or dashes) inside
     * free text such as exception messages, plus credentials: `Authorization` / `Cookie` header values (to the end of the
     * line), bearer tokens, JWTs and secret query parameters (`?auth=`, `?token=`, ...). Key-based rules (redactStructure())
     * cannot apply to text that has no keys.
     */
    public static function redactText(string $text): string
    {
        if ($text === '') {
            return '';
        }

        // Credentials first, so the digit and address rules below never see (and half-mask) a token's content.
        $masked = preg_replace(self::CREDENTIAL_HEADER_PATTERN, '$1$2' . self::REDACTED, $text);
        $masked = preg_replace(self::BEARER_PATTERN, 'Bearer ' . self::REDACTED, $masked ?? $text);
        $masked = preg_replace(self::JWT_PATTERN, self::REDACTED, $masked ?? $text);
        $masked = preg_replace(self::SECRET_QUERY_PARAMETER_PATTERN, '$1' . self::REDACTED, $masked ?? $text);
        $masked = preg_replace(self::EMAIL_PATTERN, self::REDACTED, $masked ?? $text);
        $masked = preg_replace_callback(
            self::IPV6_CANDIDATE_PATTERN,
            static function (array $match): string {
                return filter_var($match[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false ? self::REDACTED : $match[0];
            },
            $masked ?? $text
        );
        $masked = preg_replace(self::IPV4_PATTERN, self::REDACTED, $masked ?? $text);
        $masked = preg_replace_callback(
            self::PHONE_PATTERN,
            static function (array $match): string {
                return isset($match['date']) && $match['date'] !== '' ? $match[0] : self::REDACTED;
            },
            $masked ?? $text
        );

        return $masked ?? $text;
    }

    /**
     * JSON -> redactStructure() re-encoded; anything else -> redactText(). Used where a body must stay readable
     * for diagnostics (HTML error pages), unlike redactJsonString(), which replaces non-JSON with a byte count.
     */
    public static function redactPayload(string $payload, int $maxBytes = 16384): string
    {
        if ($payload === '') {
            return '';
        }

        $decoded = json_decode($payload, true);

        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return self::truncate(self::redactText($payload), $maxBytes);
        }

        $redacted = self::redactStructure($decoded);
        $reencoded = json_encode($redacted, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return self::truncate($reencoded === false ? self::REDACTED : $reencoded, $maxBytes);
    }

    /** UTF-8-safe byte truncation without mbstring: cut, then drop a trailing incomplete sequence. */
    public static function truncate(string $text, int $maxBytes): string
    {
        if (strlen($text) <= $maxBytes) {
            return $text;
        }

        $truncated = substr($text, 0, $maxBytes);
        $length = strlen($truncated);
        $i = $length;

        while ($i > 0 && (ord($truncated[$i - 1]) & 0xC0) === 0x80) {
            $i--;
        }

        if ($i > 0) {
            $leadByte = ord($truncated[$i - 1]);
            $sequenceLength = self::utf8SequenceLength($leadByte);

            if ($sequenceLength > 1 && ($length - ($i - 1)) < $sequenceLength) {
                $truncated = substr($truncated, 0, $i - 1);
            }
        }

        return $truncated;
    }

    private static function utf8SequenceLength(int $byte): int
    {
        if ($byte < 0x80) {
            return 1;
        }
        if (($byte & 0xE0) === 0xC0) {
            return 2;
        }
        if (($byte & 0xF0) === 0xE0) {
            return 3;
        }
        if (($byte & 0xF8) === 0xF0) {
            return 4;
        }

        return 1;
    }

    public static function redactJsonString(string $payload): string
    {
        if ($payload === '') {
            return '';
        }

        $decoded = json_decode($payload, true);

        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            /* Not valid JSON (e.g. an HTML error page from a misrouted response). Returning the raw body would defeat
               the purpose of redaction, so we emit a short length-only marker instead. */
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

        if (preg_match(self::IP_KEY_PATTERN, $normalized) === 1) {
            return true;
        }

        foreach (self::SENSITIVE_KEY_FRAGMENTS as $fragment) {
            if (str_contains($normalized, $fragment)) {
                return true;
            }
        }

        $words = preg_split('/[^A-Za-z0-9]+|(?<=[a-z0-9])(?=[A-Z])/', $key, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($words === false ? [] : $words as $word) {
            if (in_array(strtolower($word), self::SENSITIVE_KEY_WORDS, true)) {
                return true;
            }
        }

        return false;
    }
}
