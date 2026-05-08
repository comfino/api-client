<?php

declare(strict_types=1);

namespace Comfino\Api\Request;

use Comfino\Api\Dto\Payment\LoanQueryCriteria;
use Comfino\Api\Request;
use Comfino\Api\Dto\Payment\AllowedProductConfig;

/**
 * Financial products listing request.
 */
class GetFinancialProducts extends Request
{
    private ?array $allowedProductsConfig;

    /**
     * @param LoanQueryCriteria $queryCriteria
     */
    public function __construct(LoanQueryCriteria $queryCriteria)
    {
        $this->setRequestMethod('GET');
        $this->setApiEndpointPath('financial-products');
        $this->allowedProductsConfig = $queryCriteria->allowedProductsConfig;
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
    }

    /**
     * @inheritDoc
     */
    protected function prepareRequestBody(): ?array
    {
        return null;
    }

    protected function getApiEndpointUri(string $apiHost, int $apiVersion): string
    {
        $uri = parent::getApiEndpointUri($apiHost, $apiVersion);

        if (!empty($this->allowedProductsConfig)) {
            $configs = [];
            foreach ($this->allowedProductsConfig as $i => $config) {
                $entry = ['type' => (string) $config->type];
                if ($config->maxTerm !== null) {
                    $entry['maxTerm'] = $config->maxTerm;
                }
                if ($config->minTerm !== null) {
                    $entry['minTerm'] = $config->minTerm;
                }
                if ($config->terms !== null) {
                    $entry['terms'] = $config->terms;
                }
                $configs[$i] = $entry;
            }
            $separator = str_contains($uri, '?') ? '&' : '?';
            $uri .= $separator . http_build_query(['allowedProductsConfig' => $configs]);
        }

        return $uri;
    }
}
