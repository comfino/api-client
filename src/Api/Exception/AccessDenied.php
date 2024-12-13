<?php

namespace Comfino\Api\Exception;

use Comfino\Api\HttpErrorExceptionInterface;

class AccessDenied extends \RuntimeException implements HttpErrorExceptionInterface
{
    /** @var string */
    private string $url;
    /** @var string */
    private string $requestBody;

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, string $url = '', string $requestBody = '')
    {
        parent::__construct($message, $code, $previous);

        $this->url = $url;
        $this->requestBody = $requestBody;
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
        return '';
    }

    public function setResponseBody(string $responseBody): void
    {
    }

    public function getStatusCode(): int
    {
        return 403;
    }
}
