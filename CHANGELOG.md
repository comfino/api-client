# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.0] - 2026-05-25

> **This is the final release of `comfino/api-client`.** The library is now End of Life (EOL) and the repository has been archived. Migrate to [`comfino/php-api-client`](https://packagist.org/packages/comfino/php-api-client).

### Added
- New `AllowedProductConfig` DTO (`src/Api/Dto/Payment/AllowedProductConfig.php`) — holds per-product term constraints (`type`, `maxTerm`, `minTerm`, `terms`).
- `allowedProductsConfig` optional query parameter for `GET /v1/financial-products` (`LoanQueryCriteria`) — appended as a PHP/URL-style indexed array (`allowedProductsConfig[n][type]`, `allowedProductsConfig[n][maxTerm]`, …).
- `allowedProductsConfig` optional body field for `POST /v1/orders` — serialized as a JSON array of objects; only non-null constraint fields are included.
- `getAllowedProductsConfig(): ?array` method on `OrderInterface` and `Order`.
- New `reportShopEnvironment(ShopEnvironmentReport $report): bool` method on `Client` for reporting shop platform metadata server-to-server (fire-and-forget, returns `false` on any error).
- New `ShopEnvironmentReport` and `ShopTheme` DTOs (`src/Api/Dto/Plugin/`) describing the shop platform, theme, locale, currency, capabilities, and optional metadata.
- New `SensitiveDataRedactor` utility class (`src/Api/SensitiveDataRedactor.php`) that masks PII fields (e-mail, phone, tax ID, address, credentials, etc.) in JSON payloads — safe to use before logging or forwarding to error-tracking systems.
- New `SensitiveHttpExceptionTrait` — applied to all HTTP exception classes; adds `getRedactedRequestBody()` / `getRedactedResponseBody()` methods and overrides `__debugInfo()` so that `var_dump` / `print_r` / Sentry default serializer never leak customer PII.
- New `UrlValidator` utility (`src/Api/UrlValidator.php`) — validates callback URLs passed as HTTP headers (`Comfino-Recalculation-Url`, `Comfino-Cache-Invalidate-Url`, `Comfino-Configuration-Url`) for valid `http`/`https` scheme and absence of header-injection control characters.
- HTTP header name/value injection guard in `Client::addCustomHeader()` — rejects names with non-token characters and values containing CR/LF/NUL.

### Changed
- `generateHash()` in `CreateOrder` now throws `RequestValidationError` instead of silently returning an empty string when JSON serialization fails, preventing an attacker-replayable empty-hash signature.
- `Json` serializer raises `RequestValidationError` / `ResponseValidationError` when `json_encode()` returns `false` (defensive fallback for transpiled PHP 7.1 runtimes).
- Order ID is now URL-encoded (`rawurlencode`) in `GetOrder` and `CancelOrder` request paths to prevent path-segment injection.

### Fixed
- Callback URLs passed to `GetPaywall` and `IsShopAccountActive` are now validated before being forwarded as HTTP headers, preventing header-smuggling via malformed URLs.

### Deprecated
- This package (`comfino/api-client`) is deprecated in favour of [`comfino/php-api-client`](https://packagist.org/packages/comfino/php-api-client). No further releases are planned.

## [1.1.2] - 2026-01-15

### Added
- New exception classes for specific HTTP status codes ([FN-14003](https://github.com/comfino/api-client/commit/f49ca05)):
  - `Conflict` for HTTP 409 responses
  - `Forbidden` for HTTP 403 responses
  - `MethodNotAllowed` for HTTP 405 responses
  - `NotFound` for HTTP 404 responses

### Fixed
- API response errors handling with improved HTTP status codes processing logic ([FN-14003](https://github.com/comfino/api-client/commit/f49ca05)).

## [1.1.1] - 2025-12-09

### Added
- GitHub Actions CI/CD workflow for automated testing across PHP 8.2, 8.3, and 8.4 ([FN-13803](https://github.com/comfino/api-client/commit/5e9982b)).
- Request validation headers for order creation: `Comfino-Cart-Hash`, `Comfino-Customer-Hash`, and `Comfino-Order-Signature` ([FN-13803](https://github.com/comfino/api-client/commit/5e9982b)).
- Instance-level caching for prepared request bodies to prevent test contamination.

### Changed
- Package description simplified to "Standard PHP API client library".
- Test namespace updated from `Comfino\` to `Comfino\Tests\` for better organization.
- JSON serialization now uses `JSON_PRESERVE_ZERO_FRACTION` flag for deterministic hash generation ([FN-13803](https://github.com/comfino/api-client/commit/5e9982b)).
- `CreateOrder` constructor now requires API key parameter for signature generation ([FN-13803](https://github.com/comfino/api-client/commit/5e9982b)).
- README.md structure improvements and badge reordering.
- Composer configuration updated ([FN-13803](https://github.com/comfino/api-client/commit/81024fb)).

### Fixed
- Static variable caching issue in request classes replaced with instance properties.
- Test isolation issues by properly namespacing test classes.
- Security improvements in response handling: better null response handling and modern PHP string functions ([FN-13803](https://github.com/comfino/api-client/commit/814834d)).

## [1.1.0] - 2025-10-24

### Added
- New `validateOrder()` method for order validation without creating a loan application ([FN-13701](https://github.com/comfino/api-client/commit/b4405a9)).
- New `ValidateOrder` response class for order validation results.
- New financial product type `INSTANT_PAYMENTS` in `LoanTypeEnum` ([FN-13701](https://github.com/comfino/api-client/commit/7c647a6)).
- New financial product `PAY_IN_PARTS` ([FN-13224](https://github.com/comfino/api-client/commit/6b319d2)).
- Automatically generated `Comfino-Track-Id` HTTP header for debugging purposes ([FN-13477](https://github.com/comfino/api-client/commit/b33631a)).
- Updated widget types request with new API and widget support ([FN-11851](https://github.com/comfino/api-client/commit/db194fb)).
- `creditorName` field to `GetFinancialProducts` response structure ([FN-12669](https://github.com/comfino/api-client/commit/4409f51)).
- `recalculationUrl` parameter to `getPaywall()` request ([FN-12669](https://github.com/comfino/api-client/commit/39ed242)).
- Improved paywall frontend API v2 support ([FN-12669](https://github.com/comfino/api-client/commit/cfa9b19)).
- Debug mode support: storing request objects for debugging ([FN-11769](https://github.com/comfino/api-client/commit/cc379fc)).
- New `getFinancialProductDetails()` method for retrieving detailed product information ([FN-11769](https://github.com/comfino/api-client/commit/82f9796)).
- LEASING financial product support with dedicated API endpoints ([FN-11769](https://github.com/comfino/api-client/commit/ec83079)).
- Extended cart item types: `ADDITIONAL_FEE` and `DISCOUNT` ([FN-11769](https://github.com/comfino/api-client/commit/f18d7d5)).
- Custom HTTP headers support ([FN-11769](https://github.com/comfino/api-client/commit/a50d0b0)).
- HTTP status codes to exception classes for better error handling ([FN-11769](https://github.com/comfino/api-client/commit/ce26990)).
- Comprehensive unit tests for new features and improved test coverage.

### Changed
- Updated sandbox API URL ([FN-11851](https://github.com/comfino/api-client/commit/1a16ad3)).
- Improved data validation and sanitization ([FN-12669](https://github.com/comfino/api-client/commit/cfa9b19)).
- Enhanced security improvements and code simplifications ([FN-12669](https://github.com/comfino/api-client/commit/cfa9b19)).
- Updated request/response data structures for leasing financial product ([FN-11769](https://github.com/comfino/api-client/commit/f3faa2e)).
- Improved error handling throughout the client ([FN-11769](https://github.com/comfino/api-client/commit/b76ae65)).
- Updated delivery cost structure with net value, tax rate, and tax value ([FN-11769](https://github.com/comfino/api-client/commit/26d41fc)).
- Renamed field `deliveryNetCost` in cart structure ([FN-11769](https://github.com/comfino/api-client/commit/db7eb6f)).
- Updated external dependencies ([FN-13701](https://github.com/comfino/api-client/commit/d1d52cf)).
- Code clean up and refactoring ([FN-13701](https://github.com/comfino/api-client/commit/84ae3cc), [FN-12669](https://github.com/comfino/api-client/commit/cae72c0)).

### Fixed
- Bug in API client ([FN-12669](https://github.com/comfino/api-client/commit/1b8e8b0)).
- Bug in `Comfino-Configuration-Url` HTTP header value setting ([FN-12669](https://github.com/comfino/api-client/commit/243a208)).
- Multiple bugs in request/response data structures for leasing ([FN-11769](https://github.com/comfino/api-client/commit/9b98818)).
- Field name in order create request body ([FN-11769](https://github.com/comfino/api-client/commit/57f4329)).

## [1.0.0] - 2024-01-31

### Added
- Initial release of Comfino API Client library.
- PSR-18 compliant HTTP client implementation.
- PSR-7 HTTP message interfaces support.
- Core API methods:
  - `createOrder()` - Create new payment orders
  - `getOrder()` - Retrieve order details
  - `cancelOrder()` - Cancel existing orders
  - `getFinancialProducts()` - Get available financial products
  - `getProductTypes()` - Retrieve product types
  - `getWidgetKey()` - Get widget authentication key
  - `getWidgetTypes()` - Retrieve available widget types
  - `isShopAccountActive()` - Check shop account status
- Request/Response architecture with dedicated classes for each operation.
- Exception hierarchy for error handling:
  - `RequestValidationError` (HTTP 400)
  - `AuthorizationError` (HTTP 401)
  - `AccessDenied` (HTTP 402-405)
  - `ServiceUnavailable` (HTTP 500+)
- Environment support: Production and Sandbox modes.
- JSON serializer for request/response data.
- Dual DTO layers: API layer and Shop domain layer.
- Shop integration interfaces: `OrderInterface`, `CartInterface`, `CustomerInterface`
- Trait-based code sharing: `CartTrait`, `CustomerTrait`
- Loan query criteria with filtering options.
- Support for PHP 8.2+.
- Comprehensive PHPUnit test suite.
- Docker development environment.
- Composer scripts for testing and development.
- BSD-3-Clause license.

[Unreleased]: https://github.com/comfino/api-client/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/comfino/api-client/compare/v1.1.2...v1.2.0
[1.1.2]: https://github.com/comfino/api-client/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/comfino/api-client/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/comfino/api-client/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/comfino/api-client/releases/tag/v1.0.0
