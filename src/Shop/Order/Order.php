<?php

namespace Comfino\Shop\Order;

readonly class Order implements OrderInterface
{
    /**
     * @param string $id
     * @param string $returnUrl
     * @param LoanParametersInterface $loanParameters
     * @param CartInterface $cart
     * @param CustomerInterface $customer
     * @param string|null $notifyUrl
     * @param SellerInterface|null $seller
     * @param string|null $accountNumber
     * @param string|null $transferTitle
     */
    public function __construct(
        private string $id,
        private string $returnUrl,
        private LoanParametersInterface $loanParameters,
        private CartInterface $cart,
        private CustomerInterface $customer,
        private ?string $notifyUrl = null,
        private ?SellerInterface $seller = null,
        private ?string $accountNumber = null,
        private ?string $transferTitle = null
    ) { }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getNotifyUrl(): ?string
    {
        return $this->notifyUrl !== null ? trim(strip_tags($this->notifyUrl)) : null;
    }

    /**
     * @inheritDoc
     */
    public function getReturnUrl(): string
    {
        return trim(strip_tags($this->returnUrl));
    }

    /**
     * @inheritDoc
     */
    public function getLoanParameters(): LoanParametersInterface
    {
        return $this->loanParameters;
    }

    /**
     * @inheritDoc
     */
    public function getCart(): CartInterface
    {
        return $this->cart;
    }

    /**
     * @inheritDoc
     */
    public function getCustomer(): CustomerInterface
    {
        return $this->customer;
    }

    /**
     * @inheritDoc
     */
    public function getSeller(): ?SellerInterface
    {
        return $this->seller;
    }

    /**
     * @inheritDoc
     */
    public function getAccountNumber(): ?string
    {
        return $this->accountNumber !== null ? trim(html_entity_decode(strip_tags($this->accountNumber))) : null;
    }

    /**
     * @inheritDoc
     */
    public function getTransferTitle(): ?string
    {
        return $this->transferTitle !== null ? trim(html_entity_decode(strip_tags($this->transferTitle))) : null;
    }
}
