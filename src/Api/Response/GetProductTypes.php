<?php

declare(strict_types=1);

namespace Comfino\Api\Response;

use Comfino\Api\Dto\Payment\LoanTypeEnum;

class GetProductTypes extends Base
{
    /** @var LoanTypeEnum[] */
    public readonly array $productTypes;
    /** @var array<string, string> Internal display name keyed by product type. */
    public readonly array $productTypesWithNames;
    /** @var array<string, string> Public (customer-facing) display name keyed by product type. */
    public readonly array $productTypesWithPublicNames;

    /**
     * @inheritDoc
     */
    protected function processResponseBody(array|string|bool|null|float|int $deserializedResponseBody): void
    {
        $this->checkResponseType($deserializedResponseBody, 'array');

        $productTypesWithNames = [];
        $productTypesWithPublicNames = [];

        foreach ($deserializedResponseBody as $productType => $names) {
            $this->checkResponseType($names, 'array', $productType);

            /** @var $names string[] */
            [$internalName, $publicName] = $names;

            $productTypesWithNames[$productType] = $internalName;
            $productTypesWithPublicNames[$productType] = $publicName;
        }

        $this->productTypesWithNames = $productTypesWithNames;
        $this->productTypesWithPublicNames = $productTypesWithPublicNames;
        $this->productTypes = array_map(
            static fn (string $productType): LoanTypeEnum => LoanTypeEnum::from($productType, false),
            array_keys($deserializedResponseBody)
        );
    }
}
