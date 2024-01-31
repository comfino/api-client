<?php

namespace Comfino\Api\Dto\Payment;

readonly class LoanQueryCriteria
{
    /** @var int */
    public int $loanAmount;
    /** @var int|null */
    public ?int $loanTerm;
    /** @var LoanTypeEnum|null */
    public ?LoanTypeEnum $loanType;
    /** @var LoanTypeEnum[]|null */
    public ?array $productTypes;

    /**
     * @param int $loanAmount
     * @param int|null $loanTerm
     * @param LoanTypeEnum|null $loanType
     * @param LoanTypeEnum[]|null $productTypes
     */
    public function __construct(int $loanAmount, ?int $loanTerm = null, ?LoanTypeEnum $loanType = null, ?array $productTypes = null)
    {
        $this->loanAmount = $loanAmount;
        $this->loanTerm = $loanTerm;
        $this->loanType = $loanType;
        $this->productTypes = $productTypes;
    }
}
