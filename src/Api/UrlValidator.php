<?php

declare(strict_types=1);

namespace Comfino\Api;

use Comfino\Api\Exception\RequestValidationError;

/**
 * Validates shop-supplied URLs before they are forwarded to the Comfino API as request headers (Comfino-Recalculation-Url,
 * Comfino-Cache-Invalidate-Url, Comfino-Configuration-Url). Without this guard a malformed URL with embedded CR/LF could
 * be smuggled into the HTTP header stream by lax PSR-7 adapters commonly bundled with legacy PHP 7.1 plugins (Guzzle 6,
 * Slim 3, custom impls).
 *
 * SSRF risk against the Comfino API server itself is out of scope for this client - the API is expected to apply its
 * own outbound-fetch restrictions - but rejecting non-HTTP(S) schemes here narrows the attack surface.
 */
final class UrlValidator
{
    /**
     * Asserts that the supplied callback URL is a syntactically valid http(s) URL and contains no control characters
     * that could enable header smuggling.
     *
     * @throws RequestValidationError when the URL is malformed or uses a disallowed scheme.
     */
    public static function assertValidCallbackUrl(string $url, string $headerName): void
    {
        if (preg_match('/[\r\n\x00]/', $url) === 1) {
            throw new RequestValidationError(
                sprintf('Callback URL for "%s" contains illegal control characters.', $headerName)
            );
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new RequestValidationError(
                sprintf('Callback URL for "%s" is not a valid URL.', $headerName)
            );
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (!is_string($scheme) || !in_array(strtolower($scheme), ['http', 'https'], true)) {
            throw new RequestValidationError(
                sprintf('Callback URL for "%s" must use http or https scheme.', $headerName)
            );
        }
    }
}
