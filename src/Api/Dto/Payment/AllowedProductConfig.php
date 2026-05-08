<?php

declare(strict_types=1);

namespace Comfino\Api\Dto\Payment;

/**
 * Single entry in the allowedProductsConfig constraint list.
 * Pass only the constraints you need; omitted fields apply no restriction.
 *
 * @property int[]|null $terms Explicit whitelist of allowed instalment counts.
 */
readonly class AllowedProductConfig
{
    public function __construct(
        public LoanTypeEnum $type,
        public ?int $maxTerm = null,
        public ?int $minTerm = null,
        public ?array $terms = null
    ) {}
}