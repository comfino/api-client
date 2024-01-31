<?php

namespace Comfino\Api\Dto\Order;

use Comfino\Api\Dto\Payment\LoanTypeEnum;

readonly class LoanParameters
{
    /** @var int */
    public int $amount;
    /** @var int|null */
    public ?int $maxAmount;
    /** @var int */
    public int $term;
    /** @var LoanTypeEnum */
    public LoanTypeEnum $type;
    /** @var LoanTypeEnum[]|null */
    public ?array $allowedProductTypes;

    /**
     * @param int $amount
     * @param int|null $maxAmount
     * @param int $term
     * @param LoanTypeEnum $type
     * @param LoanTypeEnum[]|null $allowedProductTypes
     */
    public function __construct(
        int $amount,
        ?int $maxAmount,
        int $term,
        LoanTypeEnum $type,
        ?array $allowedProductTypes
    ) {
        $this->amount = $amount;
        $this->maxAmount = $maxAmount;
        $this->term = $term;
        $this->type = $type;
        $this->allowedProductTypes = $allowedProductTypes;
    }
}
