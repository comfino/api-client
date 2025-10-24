<?php

declare(strict_types=1);

namespace Comfino\Api\Response;

use Comfino\Api\Dto\Payment\LoanTypeEnum;

class GetProductTypes extends Base
{
    /** @var LoanTypeEnum[] */
    public readonly array $productTypes;
    /** @var string[] */
    public readonly array $productTypesWithNames;

    /**
     * @inheritDoc
     */
    protected function processResponseBody(array|string|bool|null|float|int $deserializedResponseBody): void
    {
        $this->checkResponseType($deserializedResponseBody, 'array');

        $this->productTypesWithNames = $deserializedResponseBody;
        $this->productTypes = array_map(
            static fn (string $productType): LoanTypeEnum => LoanTypeEnum::from($productType, false),
            array_keys($deserializedResponseBody)
        );
    }
}
