<?php

namespace Comfino\Api\Response;

class GetPaywallItemDetails extends Base
{
    /** @var string */
    public readonly string $productDetails;
    /** @var string */
    public readonly string $listItemData;

    /**
     * @inheritDoc
     */
    protected function processResponseBody(array|string|bool|null|float|int $deserializedResponseBody): void
    {
        $this->checkResponseType($deserializedResponseBody, 'array');
        $this->checkResponseStructure($deserializedResponseBody, ['productDetails', 'listItemData']);
        $this->checkResponseType($deserializedResponseBody['productDetails'], 'string');
        $this->checkResponseType($deserializedResponseBody['listItemData'], 'string');

        $this->productDetails = $deserializedResponseBody['productDetails'];
        $this->listItemData = $deserializedResponseBody['listItemData'];
    }
}
