<?php

declare(strict_types=1);

namespace Comfino\Api\Response;

class GetWidgetKey extends Base
{
    /** @var string */
    public readonly string $widgetKey;

    /**
     * @inheritDoc
     */
    protected function processResponseBody(array|string|bool|null|float|int $deserializedResponseBody): void
    {
        $this->checkResponseType($deserializedResponseBody, 'string');

        $this->widgetKey = $deserializedResponseBody;
    }
}
