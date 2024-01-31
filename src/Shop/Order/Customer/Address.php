<?php

namespace Comfino\Shop\Order\Customer;

class Address implements AddressInterface
{
    /** @var string|null */
    private ?string $street;
    /** @var string|null */
    private ?string $buildingNumber;
    /** @var string|null */
    private ?string $apartmentNumber;
    /** @var string|null */
    private ?string $postalCode;
    /** @var string|null */
    private ?string $city;
    /** @var string|null */
    private ?string $countryCode;

    /**
     * @param string|null $street
     * @param string|null $buildingNumber
     * @param string|null $apartmentNumber
     * @param string|null $postalCode
     * @param string|null $city
     * @param string|null $countryCode
     */
    public function __construct(
        ?string $street = null,
        ?string $buildingNumber = null,
        ?string $apartmentNumber = null,
        ?string $postalCode = null,
        ?string $city = null,
        ?string $countryCode = null
    ) {
        $this->street = $street;
        $this->buildingNumber = $buildingNumber;
        $this->apartmentNumber = $apartmentNumber;
        $this->postalCode = $postalCode;
        $this->city = $city;
        $this->countryCode = $countryCode;
    }

    /**
     * @inheritDoc
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @inheritDoc
     */
    public function getBuildingNumber(): ?string
    {
        return $this->buildingNumber;
    }

    /**
     * @inheritDoc
     */
    public function getApartmentNumber(): ?string
    {
        return $this->apartmentNumber;
    }

    /**
     * @inheritDoc
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @inheritDoc
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @inheritDoc
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }
}
