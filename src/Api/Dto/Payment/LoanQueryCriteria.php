<?php

declare(strict_types=1);

namespace Comfino\Api\Dto\Payment;

readonly class LoanQueryCriteria
{
    /** @var int */
    public int $loanAmount;
    /** @var int|null */
    public ?int $loanTerm;
    /** @var LoanTypeEnum|null */
    public ?LoanTypeEnum $loanType;
    /** @var int|null */
    public ?int $priceModifier;
    /** @var LoanTypeEnum[]|null */
    public ?array $productTypes;
    /** @var string|null */
    public ?string $taxId;
    /** @var AllowedProductConfig[]|null */
    public ?array $allowedProductsConfig;

    /**
     * @param int $loanAmount
     * @param int|null $loanTerm
     * @param LoanTypeEnum|null $loanType
     * @param int|null $priceModifier
     * @param LoanTypeEnum[]|null $productTypes
     * @param string|null $taxId
     * @param AllowedProductConfig[]|null $allowedProductsConfig
     */
    public function __construct(int $loanAmount, ?int $loanTerm = null, ?LoanTypeEnum $loanType = null, ?int $priceModifier = null, ?array $productTypes = null, ?string $taxId = null, ?array $allowedProductsConfig = null)
    {
        $this->loanAmount = $loanAmount;
        $this->loanTerm = $loanTerm;
        $this->loanType = $loanType;
        $this->priceModifier = $priceModifier;
        $this->productTypes = $productTypes;
        $this->taxId = $taxId;
        $this->allowedProductsConfig = $allowedProductsConfig;
    }
}
