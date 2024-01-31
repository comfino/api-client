<?php

namespace Comfino\Api\Response;

use Comfino\Api\Exception\AccessDenied;
use Comfino\Api\Exception\AuthorizationError;
use Comfino\Api\Exception\RequestValidationError;
use Comfino\Api\Exception\ServiceUnavailable;
use Comfino\Api\Response;
use Psr\Http\Message\ResponseInterface;

class Base extends Response
{
    /**
     * @param ResponseInterface $response
     * @throws RequestValidationError
     * @throws AuthorizationError
     * @throws AccessDenied
     * @throws ServiceUnavailable
     */
    public function __construct(ResponseInterface $response)
    {
        $this->initFromPsrResponse($response);
    }

    /**
     * @inheritDoc
     */
    protected function processResponseBody(array|string|bool|null $deserializedResponseBody): void
    {
    }
}
