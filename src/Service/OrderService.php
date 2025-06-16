<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CartService $cartService
    ) {}

    public function createOrderFromCart(Cart $cart, Order $order): Order
    {
        $this->entityManager->persist($order);

        foreach ($cart->getCartItems() as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setOrderRef($order);
            $orderItem->setProduct($cartItem->getProduct());
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setPrice($cartItem->getProduct()->getPrice());

            $order->addOrderItem($orderItem);
            $this->entityManager->persist($orderItem);

            // Mettre à jour le stock
            $product = $cartItem->getProduct();
            $product->setStock($product->getStock() - $cartItem->getQuantity());
        }

        $this->entityManager->flush();

        // Vider le panier
        $this->cartService->clearCart();

        return $order;
    }

    public function updateOrderStatus(Order $order, string $status): void
    {
        $order->setStatus($status);
        $this->entityManager->flush();
    }
}
