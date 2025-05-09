<?php

namespace Comfino\Shop\Order;

trait CartTrait
{
    protected function getCartAsArray(CartInterface $cart): array
    {
        $products = [];
        $cartTotal = 0;

        foreach ($cart->getItems() as $cartItem) {
            $products[] = array_filter(
                [
                    'name' => $cartItem->getProduct()->getName(),
                    'quantity' => $cartItem->getQuantity(),
                    'price' => $cartItem->getProduct()->getPrice(),
                    'photoUrl' => $cartItem->getProduct()->getPhotoUrl(),
                    'ean' => $cartItem->getProduct()->getEan(),
                    'externalId' => $cartItem->getProduct()->getId(),
                    'category' => $cartItem->getProduct()->getCategory(),
                    'netPrice' => $cartItem->getProduct()->getNetPrice(),
                    'vatRate' => $cartItem->getProduct()->getTaxRate(),
                    'vatAmount' => $cartItem->getProduct()->getTaxValue(),
                ],
                static fn ($value): bool => $value !== null
            );

            $cartTotal += ($cartItem->getProduct()->getPrice() * $cartItem->getQuantity());
        }

        $cartTotalWithDelivery = $cartTotal + ($cart->getDeliveryCost() ?? 0);
        $cartTotalItemsSumDifference = (int) ($cart->getTotalAmount() - $cartTotalWithDelivery);

        if ($cartTotalWithDelivery > $cart->getTotalAmount()) {
            // Add discount item to the list - problems with cart items value and order total value inconsistency.
            $products[] = [
                'name' => 'Rabat',
                'quantity' => 1,
                'price' => $cartTotalItemsSumDifference,
                'netPrice' => $cartTotalItemsSumDifference,
                'vatRate' => null,
                'vatAmount' => 0,
                'category' => 'DISCOUNT',
            ];
        } elseif ($cartTotalWithDelivery < $cart->getTotalAmount()) {
            // Add correction item to the list - problems with cart items value and order total value inconsistency.
            $products[] = [
                'name' => 'Korekta',
                'quantity' => 1,
                'price' => $cartTotalItemsSumDifference,
                'netPrice' => $cartTotalItemsSumDifference,
                'vatRate' => null,
                'vatAmount' => 0,
                'category' => 'ADDITIONAL_FEE',
            ];
        }

        return array_filter(
            [
                'products' => $products,
                'totalAmount' => $cart->getTotalAmount(),
                'deliveryCost' => $cart->getDeliveryCost(),
                'deliveryNetCost' => $cart->getDeliveryNetCost(),
                'deliveryCostVatRate' => $cart->getDeliveryCostTaxRate(),
                'deliveryCostVatAmount' => $cart->getDeliveryCostTaxValue(),
                'category' => $cart->getCategory(),
            ],
            static fn ($value): bool => $value !== null
        );
    }
}
