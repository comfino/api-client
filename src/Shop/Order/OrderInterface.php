<?php

declare(strict_types=1);

namespace Comfino\Shop\Order;

interface OrderInterface
{
    /**
     * Shop internal order ID.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Callback URL used by Comfino API for sending notifications about transaction status changes.
     *
     * @return string|null
     */
    public function getNotifyUrl(): ?string;

    /**
     * Return URL to the shop confirmation page, where customer will be redirected from Comfino website when transaction will be finished - successfully or not.
     *
     * @return string
     */
    public function getReturnUrl(): string;

    /** @return LoanParametersInterface */
    public function getLoanParameters(): LoanParametersInterface;

    /** @return CartInterface */
    public function getCart(): CartInterface;

    /** @return CustomerInterface */
    public function getCustomer(): CustomerInterface;

    /** @return SellerInterface|null */
    public function getSeller(): ?SellerInterface;

    /** @return string|null */
    public function getAccountNumber(): ?string;

    /** @return string|null */
    public function getTransferTitle(): ?string;

    /** @return \Comfino\Api\Dto\Payment\AllowedProductConfig[]|null */
    public function getAllowedProductsConfig(): ?array;

    /**
     * Promotional code entered by the customer at the paywall display step or later, distinct from the
     * shop-wide `PROMO` flag surfaced on `GET /v1/user/settings/flags`.
     *
     * @return string|null
     */
    public function getPromoCode(): ?string;

    /**
     * Whether the order was placed in the plugin's direct redirect mode (without the paywall iframe).
     * Null means the plugin does not report the mode.
     *
     * @return bool|null
     */
    public function getDirectRedirect(): ?bool;
}
