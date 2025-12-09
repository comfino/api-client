<?php

declare(strict_types=1);

namespace Comfino\Api\Request;

use Comfino\Api\Request;
use Comfino\Shop\Order\CartTrait;
use Comfino\Shop\Order\OrderInterface;

/**
 * Loan application creation request.
 */
class CreateOrder extends Request
{
    use CartTrait;

    private ?array $preparedRequestBody = null;

    /**
     * @param OrderInterface $order Full order data (cart, loan details).
     * @param string $apiKey API key.
     * @param bool $validateOnly Flag used for order validation (if true, order is not created and only validation result is returned).
     */
    public function __construct(private readonly OrderInterface $order, string $apiKey, private readonly bool $validateOnly = false)
    {
        $this->setRequestMethod('POST');
        $this->setApiEndpointPath('orders');

        $preparedRequestBody = $this->prepareRequestBody();
        $cartHash = $this->generateHash($preparedRequestBody['cart']);
        $customerHash = $this->generateHash($preparedRequestBody['customer']);

        $this->setRequestHeaders([
            'Comfino-Cart-Hash' => $cartHash,
            'Comfino-Customer-Hash' => $customerHash,
            'Comfino-Order-Signature' => hash('sha3-256', $cartHash . $customerHash . $apiKey),
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function prepareRequestBody(): array
    {
        if ($this->preparedRequestBody !== null) {
            return $this->preparedRequestBody;
        }

        $customer = $this->order->getCustomer();

        $this->preparedRequestBody = array_filter(
            [
                // Basic order data
                'notifyUrl' => $this->order->getNotifyUrl(),
                'returnUrl' => $this->order->getReturnUrl(),
                'orderId' => $this->order->getId(),

                // Payment data
                'loanParameters' => array_filter(
                    [
                        'amount' => $this->order->getLoanParameters()->getAmount(),
                        'term' => $this->order->getLoanParameters()->getTerm(),
                        'type' => $this->order->getLoanParameters()->getType(),
                        'allowedProductTypes' => $this->order->getLoanParameters()->getAllowedProductTypes(),
                    ],
                    static fn ($value): bool => $value !== null
                ),

                // Cart with list of products
                'cart' => $this->getCartAsArray($this->order->getCart()),

                // Customer data (mandatory)
                'customer' => array_filter(
                    [
                        'firstName' => $customer->getFirstName(),
                        'lastName' => $customer->getLastName(),
                        'email' => $customer->getEmail(),
                        'phoneNumber' => $customer->getPhoneNumber(),
                        'taxId' => $customer->getTaxId(),
                        'ip' => $customer->getIp(),
                        'regular' => $customer->isRegular(),
                        'logged' => $customer->isLogged(),

                        // Customer address (optional)
                        'address' => count(
                            $address = array_filter(
                                [
                                    'street' => $customer->getAddress()?->getStreet(),
                                    'buildingNumber' => $customer->getAddress()?->getBuildingNumber(),
                                    'apartmentNumber' => $customer->getAddress()?->getApartmentNumber(),
                                    'postalCode' => $customer->getAddress()?->getPostalCode(),
                                    'city' => $customer->getAddress()?->getCity(),
                                    'countryCode' => $customer->getAddress()?->getCountryCode(),
                                ],
                                static fn ($value): bool => $value !== null
                            )
                        ) ? $address : null,
                    ],
                    static fn ($value): bool => $value !== null
                ),

                // Seller data (optional)
                'seller' => count(
                    $seller = array_filter(
                        ['taxId' => $this->order->getSeller()?->getTaxId()],
                        static fn ($value): bool => $value !== null
                    )
                ) ? $seller : null,

                // Extra data (optional)
                'accountNumber' => $this->order->getAccountNumber(),
                'transferTitle' => $this->order->getTransferTitle(),
                'simulation' => $this->validateOnly ?: null,
            ],
            static fn ($value): bool => $value !== null
        );

        return $this->preparedRequestBody;
    }

    private function generateHash(array $data): string
    {
        try {
            return hash('sha3-256', json_encode($data, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION));
        } catch (\JsonException) {
            return '';
        }
    }
}
