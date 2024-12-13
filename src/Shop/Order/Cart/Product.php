<?php

namespace Comfino\Shop\Order\Cart;

readonly class Product implements ProductInterface
{
    /**
     * @param string $name
     * @param int $price
     * @param string|null $id
     * @param string|null $category
     * @param string|null $ean
     * @param string|null $photoUrl
     * @param int[]|null $categoryIds
     * @param int|null $netPrice
     * @param int|null $taxRate
     * @param int|null $taxValue
     */
    public function __construct(
        private string $name,
        private int $price,
        private ?string $id = null,
        private ?string $category = null,
        private ?string $ean = null,
        private ?string $photoUrl = null,
        private ?array $categoryIds = null,
        private ?int $netPrice = null,
        private ?int $taxRate = null,
        private ?int $taxValue = null
    ) { }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return trim(html_entity_decode(strip_tags($this->name)));
    }

    /**
     * @inheritDoc
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @inheritDoc
     */
    public function getNetPrice(): ?int
    {
        return $this->netPrice;
    }

    /**
     * @inheritDoc
     */
    public function getTaxRate(): ?int
    {
        return $this->taxRate;
    }

    /**
     * @inheritDoc
     */
    public function getTaxValue(): ?int
    {
        return $this->taxValue;
    }

    /**
     * @inheritDoc
     */
    public function getId(): ?string
    {
        return $this->id !== null ? trim(strip_tags($this->id)) : null;
    }

    /**
     * @inheritDoc
     */
    public function getCategory(): ?string
    {
        return $this->category !== null ? trim(strip_tags($this->category)) : null;
    }

    /**
     * @inheritDoc
     */
    public function getEan(): ?string
    {
        return $this->ean !== null ? trim(strip_tags($this->ean)) : null;
    }

    /**
     * @inheritDoc
     */
    public function getPhotoUrl(): ?string
    {
        return $this->photoUrl !== null ? trim(strip_tags($this->photoUrl)) : null;
    }

    /**
     * @inheritDoc
     */
    public function getCategoryIds(): ?array
    {
        return $this->categoryIds;
    }
}
