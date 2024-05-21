<?php

namespace Comfino\Api\Request;

use Comfino\Api\Dto\Payment\LoanQueryCriteria;
use Comfino\Api\Request;

class GetPaywall extends Request
{
    public function __construct(LoanQueryCriteria $queryCriteria)
    {
        $this->setRequestMethod('GET');
        $this->setApiEndpointPath('shop-plugin-paywall');
        $this->setRequestParams(array_filter([
            'loanAmount' => $queryCriteria->loanAmount,
            'loanTerm' => $queryCriteria->loanTerm,
            'loanType' => $queryCriteria->loanType,
            'productTypes' => $queryCriteria->productTypes,
            'taxId' => $queryCriteria->taxId,
        ], static fn ($value): bool => $value !== null));
    }

    /**
     * @inheritDoc
     */
    protected function prepareRequestBody(): ?array
    {
        return null;
    }
}
