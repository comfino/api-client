<?php

namespace Comfino\Api\Request;

use Comfino\Api\Request;

class GetPaywall extends Request
{
    public function __construct()
    {
        $this->setRequestMethod('GET');
        $this->setApiEndpointPath('shop-plugin-paywall');
    }

    /**
     * @inheritDoc
     */
    protected function prepareRequestBody(): ?array
    {
        return null;
    }
}
