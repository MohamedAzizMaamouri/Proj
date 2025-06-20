<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Service\CartService;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order')]
#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService
    ) {}

    #[Route('/checkout', name: 'app_order_checkout')]
    public function checkout(Request $request): Response
    {
        $cart = $this->cartService->getCurrentCart();

        if ($cart->getCartItems()->isEmpty()) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart_index');
        }

        $order = new Order();
        $order->setUser($this->getUser());
        $order->setTotal($cart->getTotal());

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Build shipping address from form fields
            $shippingAddress = $this->buildAddressString([
                'firstName' => $form->get('shipping_firstName')->getData(),
                'lastName' => $form->get('shipping_lastName')->getData(),
                'address' => $form->get('shipping_address')->getData(),
                'addressComplement' => $form->get('shipping_addressComplement')->getData(),
                'postalCode' => $form->get('shipping_postalCode')->getData(),
                'city' => $form->get('shipping_city')->getData(),
                'phone' => $form->get('shipping_phone')->getData(),
            ]);

            // Build billing address
            $sameAsBilling = $form->get('sameAsBilling')->getData();
            if ($sameAsBilling) {
                $billingAddress = $shippingAddress;
            } else {
                $billingAddress = $this->buildAddressString([
                    'firstName' => $form->get('billing_firstName')->getData(),
                    'lastName' => $form->get('billing_lastName')->getData(),
                    'address' => $form->get('billing_address')->getData(),
                    'addressComplement' => $form->get('billing_addressComplement')->getData(),
                    'postalCode' => $form->get('billing_postalCode')->getData(),
                    'city' => $form->get('billing_city')->getData(),
                    'phone' => $form->get('billing_phone')->getData(),
                ]);
            }

            $order->setShippingAddress($shippingAddress);
            $order->setBillingAddress($billingAddress);

            $this->orderService->createOrderFromCart($cart, $order);

            $this->addFlash('success', 'Votre commande a été passée avec succès !');

            return $this->redirectToRoute('app_order_confirmation', ['id' => $order->getId()]);
        }

        return $this->render('order/checkout.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
        ]);
    }

    private function buildAddressString(array $addressData): string
    {
        $parts = [];

        // Full name
        if ($addressData['firstName'] && $addressData['lastName']) {
            $parts[] = trim($addressData['firstName'] . ' ' . $addressData['lastName']);
        }

        // Address
        if ($addressData['address']) {
            $parts[] = $addressData['address'];
        }

        // Address complement
        if ($addressData['addressComplement']) {
            $parts[] = $addressData['addressComplement'];
        }

        // Postal code and city
        if ($addressData['postalCode'] && $addressData['city']) {
            $parts[] = $addressData['postalCode'] . ' ' . $addressData['city'];
        }

        // Phone
        if ($addressData['phone']) {
            $parts[] = 'Tél: ' . $addressData['phone'];
        }

        return implode("\n", $parts);
    }

    #[Route('/confirmation/{id}', name: 'app_order_confirmation')]
    public function confirmation(Order $order): Response
    {
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('order/confirmation.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/history', name: 'app_order_history')]
    public function history(): Response
    {
        $orders = $this->getUser()->getOrders();

        return $this->render('order/history.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/{id}', name: 'app_order_show')]
    public function show(Order $order): Response
    {
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }
}
