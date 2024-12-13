<?php

namespace Comfino\Shop\Order;

readonly class Seller implements SellerInterface
{
    /**
     * @param string|null $taxId
     */
    public function __construct(private ?string $taxId)
    {
    }

    /**
     * @inheritDoc
     */
    public function getTaxId(): ?string
    {
        return $this->taxId !== null ? trim(strip_tags($this->taxId)) : null;
    }
}
