<?php

namespace Comfino\Api\Request;

use Comfino\Api\Request;

class GetProductTypes extends Request
{
    public function __construct()
    {
        $this->setRequestMethod('GET');
        $this->setApiEndpointPath('product-types');
    }

    /**
     * @inheritDoc
     */
    protected function prepareRequestBody(): ?array
    {
        return null;
    }
}
