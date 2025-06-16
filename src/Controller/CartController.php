<?php

namespace App\Controller;

use App\Service\CartService;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart')]
#[IsGranted('ROLE_USER')]
class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService
    ) {}

    #[Route('/', name: 'app_cart_index')]
    public function index(): Response
    {
        $cart = $this->cartService->getCurrentCart();

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(int $id, Request $request, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);

        if (!$product || !$product->isIsActive()) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $quantity = max(1, (int) $request->request->get('quantity', 1));

        $this->cartService->addProduct($product, $quantity);

        $this->addFlash('success', 'Produit ajouté au panier');

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(int $id, Request $request, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $quantity = max(0, (int) $request->request->get('quantity', 0));

        if ($quantity === 0) {
            $this->cartService->removeProduct($product);
        } else {
            $this->cartService->updateQuantity($product, $quantity);
        }

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(int $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $this->cartService->removeProduct($product);

        $this->addFlash('success', 'Produit retiré du panier');

        return $this->redirectToRoute('app_cart_index');
    }
}
