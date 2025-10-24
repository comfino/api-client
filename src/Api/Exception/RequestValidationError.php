<?php

declare(strict_types=1);

namespace Comfino\Api\Exception;

use Comfino\Api\HttpErrorExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class RequestValidationError extends \LogicException implements HttpErrorExceptionInterface
{
    /** @var string */
    private string $url;
    /** @var string */
    private string $requestBody;
    /** @var string */
    private string $responseBody;
    /** @var array|string|bool|float|int|null  */
    private array|string|bool|null|float|int $deserializedResponseBody;
    private ResponseInterface $response;

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, string $url = '', string $requestBody = '', string $responseBody = '', $deserializedResponseBody = null, ResponseInterface $response = null)
    {
        parent::__construct($message, $code, $previous);

        $this->url = $url;
        $this->requestBody = $requestBody;
        $this->responseBody = $responseBody;
        $this->deserializedResponseBody = $deserializedResponseBody;
        $this->response = $response;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getRequestBody(): string
    {
        return $this->requestBody;
    }

    public function setRequestBody(string $requestBody): void
    {
        $this->requestBody = $requestBody;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }

    public function setResponseBody(string $responseBody): void
    {
        $this->responseBody = $responseBody;
    }

    public function getDeserializedResponseBody(): float|int|bool|array|string|null
    {
        return $this->deserializedResponseBody;
    }

    public function setDeserializedResponseBody(float|int|bool|array|string|null $deserializedResponseBody): void
    {
        $this->deserializedResponseBody = $deserializedResponseBody;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    public function getStatusCode(): int
    {
        return 400;
    }
}
