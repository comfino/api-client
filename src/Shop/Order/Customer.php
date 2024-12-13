<?php

namespace Comfino\Shop\Order;

use Comfino\Shop\Order\Customer\AddressInterface;

readonly class Customer implements CustomerInterface
{
    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $phoneNumber
     * @param string $ip
     * @param string|null $taxId
     * @param bool|null $isRegular
     * @param bool|null $isLogged
     * @param AddressInterface|null $address
     */
    public function __construct(
        private string $firstName,
        private string $lastName,
        private string $email,
        private string $phoneNumber,
        private string $ip,
        private ?string $taxId = null,
        private ?bool $isRegular = null,
        private ?bool $isLogged = null,
        private ?AddressInterface $address = null
    ) { }

    /**
     * @inheritDoc
     */
    public function getFirstName(): string
    {
        return trim(strip_tags($this->firstName));
    }

    /**
     * @inheritDoc
     */
    public function getLastName(): string
    {
        return trim(strip_tags($this->lastName));
    }

    /**
     * @inheritDoc
     */
    public function getEmail(): string
    {
        return trim(strip_tags($this->email));
    }

    /**
     * @inheritDoc
     */
    public function getPhoneNumber(): string
    {
        return trim(strip_tags($this->phoneNumber));
    }

    /**
     * @inheritDoc
     */
    public function getIp(): string
    {
        return trim($this->ip);
    }

    /**
     * @inheritDoc
     */
    public function getTaxId(): ?string
    {
        return $this->taxId !== null ? trim(strip_tags($this->taxId)) : null;
    }

    /**
     * @inheritDoc
     */
    public function isRegular(): ?bool
    {
        return $this->isRegular;
    }

    /**
     * @inheritDoc
     */
    public function isLogged(): ?bool
    {
        return $this->isLogged;
    }

    /**
     * @inheritDoc
     */
    public function getAddress(): ?AddressInterface
    {
        return $this->address;
    }
}
