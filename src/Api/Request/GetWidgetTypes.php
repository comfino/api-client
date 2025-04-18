<?php

namespace Comfino\Api\Request;

use Comfino\Api\Request;

class GetWidgetTypes extends Request
{
    public function __construct(private readonly bool $useNewApi)
    {
        $this->setRequestMethod('GET');
        $this->setApiEndpointPath($this->useNewApi ? 'widget' : 'widget-types');
    }

    protected function getApiEndpointUri(string $apiHost, int $apiVersion): string
    {
        if (!$this->useNewApi) {
            return parent::getApiEndpointUri($apiHost, $apiVersion);
        }

        $uri = implode('/', [trim($apiHost, " /\n\r\t\v\0"), $this->apiEndpointPath, "v$apiVersion/widget-types"]);

        if (!empty($this->requestParams)) {
            $uri .= ('?' . http_build_query($this->requestParams));
        }

        return $uri;
    }

    /**
     * @inheritDoc
     */
    protected function prepareRequestBody(): ?array
    {
        return null;
    }
}
