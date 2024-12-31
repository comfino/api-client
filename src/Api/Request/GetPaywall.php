<?php

namespace Comfino\Api\Request;

use Comfino\Api\Dto\Payment\LoanQueryCriteria;
use Comfino\Api\Request;

class GetPaywall extends Request
{
    /**
     * @param LoanQueryCriteria $queryCriteria
     * @param string|null $recalculationUrl
     */
    public function __construct(LoanQueryCriteria $queryCriteria, ?string $recalculationUrl)
    {
        $this->setRequestMethod('GET');
        $this->setApiEndpointPath('shop-plugin-paywall');
        $this->setRequestParams(
            array_filter(
                [
                    'loanAmount' => $queryCriteria->loanAmount,
                    'loanTerm' => $queryCriteria->loanTerm,
                    'loanTypeSelected' => $queryCriteria->loanType,
                    'productTypes' => ($queryCriteria->productTypes !== null ? implode(',', $queryCriteria->productTypes) : null),
                    'taxId' => $queryCriteria->taxId,
                ],
                static fn ($value): bool => $value !== null
            )
        );

        if ($recalculationUrl !== null) {
            $this->setRequestHeaders(['Comfino-Recalculation-Url' => $recalculationUrl]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function prepareRequestBody(): ?array
    {
        return null;
    }
}
