<?php

namespace Comfino\Shop\Order;

use Comfino\Shop\Order\Cart\CartItemInterface;

readonly class Cart implements CartInterface
{
    /**
     * @param CartItemInterface[] $items
     * @param int $totalAmount
     * @param int|null $deliveryCost
     * @param int|null $netDeliveryCost
     * @param int|null $deliveryCostTaxRate
     * @param int|null $deliveryCostTaxValue
     * @param string|null $category
     */
    public function __construct(
        private array $items,
        private int $totalAmount,
        private ?int $deliveryCost = null,
        private ?int $netDeliveryCost = null,
        private ?int $deliveryCostTaxRate = null,
        private ?int $deliveryCostTaxValue = null,
        private ?string $category = null
    ) { }

    /**
     * @inheritDoc
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @inheritDoc
     */
    public function getTotalAmount(): int
    {
        return $this->totalAmount;
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryCost(): ?int
    {
        return $this->deliveryCost;
    }

    public function getNetDeliveryCost(): ?int
    {
        return $this->netDeliveryCost;
    }

    public function getDeliveryCostTaxRate(): ?int
    {
        return $this->deliveryCostTaxRate;
    }

    public function getDeliveryCostTaxValue(): ?int
    {
        return $this->deliveryCostTaxValue;
    }

    /**
     * @inheritDoc
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }
}
