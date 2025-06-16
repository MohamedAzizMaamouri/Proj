<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CartService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {}

    public function getCurrentCart(): Cart
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw new \LogicException('User must be logged in to access cart');
        }

        $cart = $user->getCart();

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }

        return $cart;
    }

    public function addProduct(Product $product, int $quantity = 1): void
    {
        $cart = $this->getCurrentCart();

        // Vérifier si le produit est déjà dans le panier
        foreach ($cart->getCartItems() as $cartItem) {
            if ($cartItem->getProduct() === $product) {
                $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
                $cart->setUpdatedAt(new \DateTime());
                $this->entityManager->flush();
                return;
            }
        }

        // Ajouter un nouvel item au panier
        $cartItem = new CartItem();
        $cartItem->setCart($cart);
        $cartItem->setProduct($product);
        $cartItem->setQuantity($quantity);

        $cart->addCartItem($cartItem);
        $cart->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($cartItem);
        $this->entityManager->flush();
    }

    public function updateQuantity(Product $product, int $quantity): void
    {
        $cart = $this->getCurrentCart();

        foreach ($cart->getCartItems() as $cartItem) {
            if ($cartItem->getProduct() === $product) {
                $cartItem->setQuantity($quantity);
                $cart->setUpdatedAt(new \DateTime());
                $this->entityManager->flush();
                return;
            }
        }
    }

    public function removeProduct(Product $product): void
    {
        $cart = $this->getCurrentCart();

        foreach ($cart->getCartItems() as $cartItem) {
            if ($cartItem->getProduct() === $product) {
                $cart->removeCartItem($cartItem);
                $cart->setUpdatedAt(new \DateTime());
                $this->entityManager->remove($cartItem);
                $this->entityManager->flush();
                return;
            }
        }
    }

    public function clearCart(): void
    {
        $cart = $this->getCurrentCart();

        foreach ($cart->getCartItems() as $cartItem) {
            $this->entityManager->remove($cartItem);
        }

        $cart->getCartItems()->clear();
        $cart->setUpdatedAt(new \DateTime());
        $this->entityManager->flush();
    }
}
