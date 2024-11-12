<?php

namespace Comfino\Api\Response;

use Comfino\Api\Dto\Payment\FinancialProduct;
use Comfino\Api\Dto\Payment\LoanParameters;
use Comfino\Api\Dto\Payment\LoanTypeEnum;
use Comfino\Api\Exception\ResponseValidationError;

class GetFinancialProducts extends Base
{
    /** @var FinancialProduct[] */
    public readonly array $financialProducts;

    /**
     * @inheritDoc
     */
    protected function processResponseBody(array|string|bool|null $deserializedResponseBody): void
    {
        if (!is_array($deserializedResponseBody)) {
            throw new ResponseValidationError('Invalid response data: array expected.');
        }

        $financialProducts = [];

        foreach ($deserializedResponseBody as $financialProduct) {
            $financialProducts[] = new FinancialProduct(
                $financialProduct['name'],
                LoanTypeEnum::from($financialProduct['type']),
                $financialProduct['description'] ?? '',
                $financialProduct['icon'],
                $financialProduct['instalmentAmount'],
                $financialProduct['toPay'],
                $financialProduct['loanTerm'],
                $financialProduct['rrso'] ?? 0.0,
                $financialProduct['representativeExample'] ?? '',
                $financialProduct['remarks'] ?? '',
                array_map(
                    static fn (array $loanParams): LoanParameters => new LoanParameters(
                        $loanParams['instalmentAmount'],
                        $loanParams['toPay'],
                        $loanParams['loanTerm'],
                        $loanParams['rrso'],
                        $loanParams['initialPaymentValue'] ?? null,
                        $loanParams['initialPaymentRate'] ?? null,
                        $loanParams['redemptionPaymentValue'] ?? null,
                        $loanParams['redemptionPaymentRate'] ?? null,
                        $loanParams['offerRate'] ?? null
                    ),
                    $financialProduct['loanParameters']
                ),
                $financialProduct['initialPaymentValue'] ?? null,
                $financialProduct['initialPaymentRate'] ?? null,
                $financialProduct['redemptionPaymentValue'] ?? null,
                $financialProduct['redemptionPaymentRate'] ?? null,
                $financialProduct['offerRate'] ?? null
            );
        }

        $this->financialProducts = $financialProducts;
    }
}
