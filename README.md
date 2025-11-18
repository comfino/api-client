# Comfino API Client

[![Latest Version](https://img.shields.io/github/release/comfino/api-client.svg)](https://github.com/comfino/api-client/releases)
[![PHP Version](https://img.shields.io/packagist/php-v/comfino/api-client.svg)](https://packagist.org/packages/comfino/api-client)
[![Build Status](https://github.com/comfino/api-client/actions/workflows/tests.yml/badge.svg)](https://github.com/comfino/api-client/actions/workflows/tests.yml)
[![Software License](https://img.shields.io/badge/license-BSD%203--Clause-orange.svg)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/comfino/api-client.svg)](https://packagist.org/packages/comfino/api-client)

**Comfino API client library**

Standard PHP API client library for the Comfino payment gateway.

## Features

- PSR-18 HTTP Client compatibility
- PSR-7 HTTP Messages support
- Production and sandbox environment support

## Requirements

- PHP 8.2 or higher
- PSR-18 HTTP Client implementation
- Composer

## Installation

Install via Composer:

```bash
composer require comfino/api-client
```

## Usage

### Basic Setup

```php
use Comfino\Api\Client;

// Initialize the client.
$client = new Client(
    $requestFactory, // PSR-17 Request Factory
    $streamFactory,  // PSR-17 Stream Factory
    $httpClient,     // PSR-18 HTTP Client
    'your-api-key'
);

// Enable sandbox mode for testing.
$client->enableSandboxMode();

// Or disable it for production.
$client->disableSandboxMode();
```

### Making API calls

The Client class provides convenience methods for all API operations:

```php
use Comfino\Api\Dto\Payment\LoanQueryCriteria;
use Comfino\Shop\Order\OrderInterface;

// Create a loan application.
$order = /* OrderInterface instance */;
$response = $client->createOrder($order);
$applicationUrl = $response->applicationUrl;

// Get order details.
$orderDetails = $client->getOrder($orderId);

// Get available financial products.
$queryCriteria = new LoanQueryCriteria(/* ... */);
$products = $client->getFinancialProducts($queryCriteria);

// Check if shop account is active.
$isActive = $client->isShopAccountActive();

// Cancel an order.
$client->cancelOrder($orderId);
```

## Architecture

### Core components

- **Client class** (`src/Api/Client.php`): Main entry point using dependency injection with PSR-18 HTTP Client interfaces.
- **Request/Response pattern**: Each API operation has dedicated classes using Command pattern.
- **Dual DTO layers**: API layer DTOs (readonly classes) and Shop domain layer (interface-based for flexibility).
- **Environment support**: Configurable API hosts for production and sandbox.

### Key design patterns

- **Abstract base classes**: `Request` and `Response` define common behavior.
- **Strategy pattern**: Pluggable serializers through `SerializerInterface` (default: JSON),
- **Interface contracts**: Shop integration through `OrderInterface`, `CartInterface`, `CustomerInterface`.
- **Trait-based sharing**: Common functionality through traits like `CartTrait`.

### Directory structure

```
src/
├── Api/          # Core API client, requests, responses, DTOs, exceptions.
├── Shop/         # Domain models for e-commerce integration.
└── Enum.php      # Base enum class for PHP version compatibility.
tests/            # PHPUnit tests with trait-based organization.
```

### Error handling

Exception hierarchy based on HTTP status codes:

- **400**: `RequestValidationError` - Invalid request data.
- **401**: `AuthorizationError` - Authentication failure.
- **402-405**: `AccessDenied` - Permission issues.
- **500+**: `ServiceUnavailable` - Server errors.

All exceptions implement `HttpErrorExceptionInterface` and preserve request/response context for debugging.

## Development

### Install dependencies

```bash
# Using the wrapper script (Docker or local).
./bin/composer install

# Update dependencies.
./bin/composer update
```

### Running tests

```bash
# Run all tests.
./bin/composer test

# Or use PHPUnit directly.
./vendor/bin/phpunit

# Run specific test.
./vendor/bin/phpunit tests/Api/ClientTest.php

# Generate coverage report (requires Xdebug).
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html coverage
```

### Docker development

```bash
# Start development environment.
docker-compose up -d

# Run commands inside container.
docker-compose exec api-client-php composer test
docker-compose exec api-client-php php -v
```

## Testing approach

- Mock HTTP clients for integration testing.
- Trait-based test organization (`ClientTestTrait`, `ReflectionTrait`).
- Comprehensive coverage of success and error scenarios.
- Validation of request construction, response parsing, and error handling.

## PSR standards

This library follows PHP Standards Recommendations:

- **PSR-4**: Autoloading
- **PSR-7**: HTTP Messages
- **PSR-18**: HTTP Client

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

This library is licensed under the BSD 3-Clause License. See the [LICENSE](LICENSE) file for details.

## Support

For bug reports and feature requests, please use the [GitHub issue tracker](https://github.com/comfino/api-client/issues).

## Contributing

Contributions are welcome! Please ensure all tests pass before submitting pull requests:
