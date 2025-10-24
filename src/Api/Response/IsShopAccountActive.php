<?php

declare(strict_types=1);

namespace Comfino\Api\Response;

class IsShopAccountActive extends Base
{
    /** @var bool */
    public readonly bool $isActive;

    /**
     * @inheritDoc
     */
    protected function processResponseBody(array|string|bool|null|float|int $deserializedResponseBody): void
    {
        $this->checkResponseType($deserializedResponseBody, 'boolean');

        $this->isActive = $deserializedResponseBody;
    }
}
