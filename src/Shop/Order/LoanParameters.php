<?php

namespace Comfino\Shop\Order;

use Comfino\Api\Dto\Payment\LoanTypeEnum;

readonly class LoanParameters implements LoanParametersInterface
{
    /**
     * @param int $amount
     * @param int|null $term
     * @param LoanTypeEnum|null $type
     * @param LoanTypeEnum[]|null $allowedProductTypes
     */
    public function __construct(
        private int $amount,
        private ?int $term = null,
        private ?LoanTypeEnum $type = null,
        private ?array $allowedProductTypes = null
    ) { }

    /**
     * @inheritDoc
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @inheritDoc
     */
    public function getTerm(): ?int
    {
        return $this->term;
    }

    /**
     * @inheritDoc
     */
    public function getType(): ?LoanTypeEnum
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function getAllowedProductTypes(): ?array
    {
        return $this->allowedProductTypes;
    }
}
