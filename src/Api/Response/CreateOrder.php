<?php

declare(strict_types=1);

namespace Comfino\Api\Response;

class CreateOrder extends Base
{
    /** @var string */
    public readonly string $status;
    /** @var string */
    public readonly string $externalId;
    /** @var string */
    public readonly string $applicationUrl;

    /**
     * @inheritDoc
     */
    protected function processResponseBody(array|string|bool|null|float|int $deserializedResponseBody): void
    {
        $this->checkResponseType($deserializedResponseBody, 'array');
        $this->checkResponseStructure($deserializedResponseBody, ['status', 'externalId', 'applicationUrl']);
        $this->checkResponseType($deserializedResponseBody['status'], 'string', 'status');
        $this->checkResponseType($deserializedResponseBody['externalId'], 'string', 'externalId');
        $this->checkResponseType($deserializedResponseBody['applicationUrl'], 'string', 'applicationUrl');

        $this->status = $deserializedResponseBody['status'];
        $this->externalId = $deserializedResponseBody['externalId'];
        $this->applicationUrl = $deserializedResponseBody['applicationUrl'];
    }
}
