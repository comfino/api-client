<?php

declare(strict_types=1);

namespace Comfino\Api\Dto\Payment;

readonly class LoanParameters
{
    /** @var int */
    public int $instalmentAmount;
    /** @var int */
    public int $toPay;
    /** @var int */
    public int $loanTerm;
    /** @var float */
    public float $rrso;
    /** @var int|null */
    public ?int $initialPaymentValue;
    /** @var float|null */
    public ?float $initialPaymentRate;
    /** @var int|null */
    public ?int $redemptionPaymentValue;
    /** @var float|null */
    public ?float $redemptionPaymentRate;
    /** @var float|null */
    public ?float $interest;

    /**
     * @param int $instalmentAmount
     * @param int $toPay
     * @param int $loanTerm
     * @param float $rrso
     * @param int|null $initialPaymentValue
     * @param float|null $initialPaymentRate
     * @param int|null $redemptionPaymentValue
     * @param float|null $redemptionPaymentRate
     * @param float|null $interest
     */
    public function __construct(
        int $instalmentAmount,
        int $toPay,
        int $loanTerm,
        float $rrso,
        ?int $initialPaymentValue = null,
        ?float $initialPaymentRate = null,
        ?int $redemptionPaymentValue = null,
        ?float $redemptionPaymentRate = null,
        ?float $interest = null
    )
    {
        $this->instalmentAmount = $instalmentAmount;
        $this->toPay = $toPay;
        $this->loanTerm = $loanTerm;
        $this->rrso = $rrso;
        $this->initialPaymentValue = $initialPaymentValue;
        $this->initialPaymentRate = $initialPaymentRate;
        $this->redemptionPaymentValue = $redemptionPaymentValue;
        $this->redemptionPaymentRate = $redemptionPaymentRate;
        $this->interest = $interest;
    }
}
