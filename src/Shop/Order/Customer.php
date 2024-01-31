<?php

namespace Comfino\Shop\Order;

use Comfino\Shop\Order\Customer\AddressInterface;

class Customer implements CustomerInterface
{
    /** @var string */
    private string $firstName;
    /** @var string */
    private string $lastName;
    /** @var string */
    private string $email;
    /** @var string */
    private string $phoneNumber;
    /** @var string */
    private string $ip;
    /** @var string|null */
    private ?string $taxId;
    /** @var bool|null */
    private ?bool $isRegular;
    /** @var bool|null */
    private ?bool $isLogged;
    /** @var AddressInterface|null */
    private ?AddressInterface $address;

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
        string $firstName,
        string $lastName,
        string $email,
        string $phoneNumber,
        string $ip,
        ?string $taxId = null,
        ?bool $isRegular = null,
        ?bool $isLogged = null,
        ?AddressInterface $address = null
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->ip = $ip;
        $this->taxId = $taxId;
        $this->isRegular = $isRegular;
        $this->isLogged = $isLogged;
        $this->address = $address;
    }

    /**
     * @inheritDoc
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @inheritDoc
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @inheritDoc
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @inheritDoc
     */
    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    /**
     * @inheritDoc
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @inheritDoc
     */
    public function getTaxId(): ?string
    {
        return $this->taxId;
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
