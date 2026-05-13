<?php

declare(strict_types=1);

namespace Comfino\Api\Response;

/**
 * Available creditors list response.
 */
class GetCreditors extends Base
{
    /** @var array<string, string[]> */
    public readonly array $creditors;

    /**
     * @inheritDoc
     */
    protected function processResponseBody(array|string|bool|null|float|int $deserializedResponseBody): void
    {
        $this->checkResponseType($deserializedResponseBody, 'array');

        $this->creditors = $deserializedResponseBody;
    }
}
