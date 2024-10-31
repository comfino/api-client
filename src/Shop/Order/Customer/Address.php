<?php

namespace Comfino\Shop\Order\Customer;

readonly class Address implements AddressInterface
{
    /**
     * @param string|null $street
     * @param string|null $buildingNumber
     * @param string|null $apartmentNumber
     * @param string|null $postalCode
     * @param string|null $city
     * @param string|null $countryCode
     */
    public function __construct(
        private ?string $street = null,
        private ?string $buildingNumber = null,
        private ?string $apartmentNumber = null,
        private ?string $postalCode = null,
        private ?string $city = null,
        private ?string $countryCode = null
    ) { }

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
