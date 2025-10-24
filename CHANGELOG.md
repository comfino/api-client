# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2025-10-24

### Added
- New `validateOrder()` method for order validation without creating a loan application ([FN-13701](https://github.com/comfino/api-client/commit/b4405a9))
- New `ValidateOrder` response class for order validation results
- New financial product type `INSTANT_PAYMENTS` in `LoanTypeEnum` ([FN-13701](https://github.com/comfino/api-client/commit/7c647a6))
- New financial product `PAY_IN_PARTS` ([FN-13224](https://github.com/comfino/api-client/commit/6b319d2))
- Automatically generated `Comfino-Track-Id` HTTP header for debugging purposes ([FN-13477](https://github.com/comfino/api-client/commit/b33631a))
- Updated widget types request with new API and widget support ([FN-11851](https://github.com/comfino/api-client/commit/db194fb))
- `creditorName` field to `GetFinancialProducts` response structure ([FN-12669](https://github.com/comfino/api-client/commit/4409f51))
- `recalculationUrl` parameter to `getPaywall()` request ([FN-12669](https://github.com/comfino/api-client/commit/39ed242))
- Improved paywall frontend API v2 support ([FN-12669](https://github.com/comfino/api-client/commit/cfa9b19))
- Debug mode support: storing request objects for debugging ([FN-11769](https://github.com/comfino/api-client/commit/cc379fc))
- New `getFinancialProductDetails()` method for retrieving detailed product information ([FN-11769](https://github.com/comfino/api-client/commit/82f9796))
- LEASING financial product support with dedicated API endpoints ([FN-11769](https://github.com/comfino/api-client/commit/ec83079))
- Extended cart item types: `ADDITIONAL_FEE` and `DISCOUNT` ([FN-11769](https://github.com/comfino/api-client/commit/f18d7d5))
- Custom HTTP headers support ([FN-11769](https://github.com/comfino/api-client/commit/a50d0b0))
- HTTP status codes to exception classes for better error handling ([FN-11769](https://github.com/comfino/api-client/commit/ce26990))
- Comprehensive unit tests for new features and improved test coverage

### Changed
- Updated sandbox API URL ([FN-11851](https://github.com/comfino/api-client/commit/1a16ad3))
- Improved data validation and sanitization ([FN-12669](https://github.com/comfino/api-client/commit/cfa9b19))
- Enhanced security improvements and code simplifications ([FN-12669](https://github.com/comfino/api-client/commit/cfa9b19))
- Updated request/response data structures for leasing financial product ([FN-11769](https://github.com/comfino/api-client/commit/f3faa2e))
- Improved error handling throughout the client ([FN-11769](https://github.com/comfino/api-client/commit/b76ae65))
- Updated delivery cost structure with net value, tax rate, and tax value ([FN-11769](https://github.com/comfino/api-client/commit/26d41fc))
- Renamed field `deliveryNetCost` in cart structure ([FN-11769](https://github.com/comfino/api-client/commit/db7eb6f))
- Updated external dependencies ([FN-13701](https://github.com/comfino/api-client/commit/d1d52cf))
- Code clean up and refactoring ([FN-13701](https://github.com/comfino/api-client/commit/84ae3cc), [FN-12669](https://github.com/comfino/api-client/commit/cae72c0))

### Fixed
- Bug in API client ([FN-12669](https://github.com/comfino/api-client/commit/1b8e8b0))
- Bug in `Comfino-Configuration-Url` HTTP header value setting ([FN-12669](https://github.com/comfino/api-client/commit/243a208))
- Multiple bugs in request/response data structures for leasing ([FN-11769](https://github.com/comfino/api-client/commit/9b98818))
- Field name in order create request body ([FN-11769](https://github.com/comfino/api-client/commit/57f4329))

## [1.0.0] - 2024-01-31

### Added
- Initial release of Comfino API Client library
- PSR-18 compliant HTTP client implementation
- PSR-7 HTTP message interfaces support
- Core API methods:
  - `createOrder()` - Create new payment orders
  - `getOrder()` - Retrieve order details
  - `cancelOrder()` - Cancel existing orders
  - `getFinancialProducts()` - Get available financial products
  - `getProductTypes()` - Retrieve product types
  - `getWidgetKey()` - Get widget authentication key
  - `getWidgetTypes()` - Retrieve available widget types
  - `isShopAccountActive()` - Check shop account status
- Request/Response architecture with dedicated classes for each operation
- Exception hierarchy for error handling:
  - `RequestValidationError` (HTTP 400)
  - `AuthorizationError` (HTTP 401)
  - `AccessDenied` (HTTP 402-405)
  - `ServiceUnavailable` (HTTP 500+)
- Environment support: Production and Sandbox modes
- JSON serializer for request/response data
- Dual DTO layers: API layer and Shop domain layer
- Shop integration interfaces: `OrderInterface`, `CartInterface`, `CustomerInterface`
- Trait-based code sharing: `CartTrait`, `CustomerTrait`
- Loan query criteria with filtering options
- Support for PHP 8.2+
- Comprehensive PHPUnit test suite
- Docker development environment
- Composer scripts for testing and development
- BSD-3-Clause license

[Unreleased]: https://github.com/comfino/api-client/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/comfino/api-client/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/comfino/api-client/releases/tag/v1.0.0