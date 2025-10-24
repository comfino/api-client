<?php

declare(strict_types=1);

namespace Comfino\Api;

interface HttpErrorExceptionInterface
{
    public function getUrl(): string;

    public function getRequestBody(): string;

    public function setRequestBody(string $requestBody): void;

    public function getResponseBody(): string;

    public function setResponseBody(string $responseBody): void;

    public function getStatusCode(): int;
}
