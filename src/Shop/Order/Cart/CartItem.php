<?php

declare(strict_types=1);

namespace Comfino\Shop\Order\Cart;

readonly class CartItem implements CartItemInterface
{
    /**
     * @param ProductInterface $product
     * @param int $quantity
     */
    public function __construct(private ProductInterface $product, private int $quantity)
    {
    }

    /**
     * @inheritDoc
     */
    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    /**
     * @inheritDoc
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
