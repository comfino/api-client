<?php

namespace Comfino\Api;

use Comfino\Api\Exception\RequestValidationError;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * API request abstraction.
 */
abstract class Request
{
    /** @var SerializerInterface */
    private SerializerInterface $serializer;
    /** @var string */
    private string $method;
    /** @var string */
    private string $apiEndpointPath;
    /** @var array|null */
    private ?array $requestParams;

    final public function setSerializer(SerializerInterface $serializer): self
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * Returns PSR-7 compatible HTTP request object.
     *
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     * @param string $apiHost
     * @param int $apiVersion
     * @return RequestInterface
     * @throws RequestValidationError
     */
    final public function getPsrRequest(
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $apiHost,
        int $apiVersion
    ): RequestInterface {
        if (empty($this->method)) {
            throw new RequestValidationError('Invalid request data: HTTP method undefined.');
        }
        if (empty($this->apiEndpointPath)) {
            throw new RequestValidationError('Invalid request data: API endpoint path undefined.');
        }

        $request = $requestFactory->createRequest($this->method, $this->getApiEndpointUri($apiHost, $apiVersion));
        $body = $this->serializeRequestBody();

        return $body !== null ? $request->withBody($streamFactory->createStream($body)) : $request;
    }

    /**
     * @return string
     * @throws RequestValidationError
     */
    final public function __toString(): string
    {
        return ($serializedBody = $this->serializeRequestBody()) !== null ? $serializedBody : '';
    }

    /**
     * @param string $method
     * @return void
     */
    final protected function setRequestMethod(string $method): void
    {
        $this->method = strtoupper(trim($method));
    }

    /**
     * @param string $apiEndpointPath
     * @return void
     */
    final protected function setApiEndpointPath(string $apiEndpointPath): void
    {
        $this->apiEndpointPath = trim($apiEndpointPath, " /\n\r\t\v\0");
    }

    /**
     * @param array $requestParams
     * @return void
     */
    final protected function setRequestParams(array $requestParams): void
    {
        $this->requestParams = $requestParams;
    }

    /**
     * @return string|null
     * @throws RequestValidationError
     */
    final protected function serializeRequestBody(): ?string
    {
        return ($body = $this->prepareRequestBody()) !== null ? $this->serializer->serialize($body) : null;
    }

    /**
     * @param string $apiHost
     * @param int $apiVersion
     * @return string
     */
    final protected function getApiEndpointUri(string $apiHost, int $apiVersion): string
    {
        $uri = implode('/', [trim($apiHost, " /\n\r\t\v\0"), "v$apiVersion", $this->apiEndpointPath]);

        if (!empty($this->requestParams)) {
            $uri .= ('?' . http_build_query($this->requestParams));
        }

        return $uri;
    }

    /**
     * Converts API request object to the array which is ready for serialization.
     *
     * @return array|null
     */
    abstract protected function prepareRequestBody(): ?array;
}
