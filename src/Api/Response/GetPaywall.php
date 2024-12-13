<?php

namespace Comfino\Api\Response;

class GetPaywall extends Base
{
    /** @var string */
    public readonly string $paywallBody;
    /** @var string */
    public readonly string $paywallHash;

    /**
     * @inheritDoc
     */
    protected function processResponseBody(array|string|bool|null|float|int $deserializedResponseBody): void
    {
        $this->checkResponseType($deserializedResponseBody, 'array');
        $this->checkResponseStructure($deserializedResponseBody, ['paywallBody', 'paywallHash']);
        $this->checkResponseType($deserializedResponseBody['paywallBody'], 'string');
        $this->checkResponseType($deserializedResponseBody['paywallHash'], 'string');

        $this->paywallBody = $deserializedResponseBody['paywallBody'];
        $this->paywallHash = $deserializedResponseBody['paywallHash'];
    }
}
