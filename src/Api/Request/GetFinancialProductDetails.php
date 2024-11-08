<?php

namespace Comfino\Api\Request;

use Comfino\Api\Dto\Payment\LoanQueryCriteria;
use Comfino\Api\Request;
use Comfino\Shop\Order\CartInterface;
use Comfino\Shop\Order\CartTrait;

/**
 * Financial product details request.
 */
class GetFinancialProductDetails extends Request
{
    use CartTrait;

    /**
     * @param LoanQueryCriteria $queryCriteria
     * @param CartInterface $cart
     */
    public function __construct(LoanQueryCriteria $queryCriteria, private readonly CartInterface $cart)
    {
        $this->setRequestMethod('POST');
        $this->setApiEndpointPath('financial-products');
        $this->setRequestParams(
            array_filter(
                [
                    'loanAmount' => $queryCriteria->loanAmount,
                    'loanTerm' => $queryCriteria->loanTerm,
                    'loanType' => $queryCriteria->loanType,
                    'productTypes' => $queryCriteria->productTypes,
                    'taxId' => $queryCriteria->taxId,
                ],
                static fn ($value): bool => $value !== null
            )
        );
    }

    /**
     * @inheritDoc
     */
    protected function prepareRequestBody(): ?array
    {
        return $this->getCartAsArray($this->cart);
    }
}
