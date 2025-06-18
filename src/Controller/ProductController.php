<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/products')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index')]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $search = $request->query->get('search');
        $brand = $request->query->get('brand');
        $categorySlug = $request->query->get('category');

        $category = null;
        if ($categorySlug) {
            $category = $categoryRepository->findBySlug($categorySlug);
        }

        $products = $productRepository->findByFilters($search, $brand, $category);
        $brands = $productRepository->findAllBrands();
        $categories = $categoryRepository->findAllActive();

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'brands' => $brands,
            'categories' => $categories,
            'current_search' => $search,
            'current_brand' => $brand,
            'current_category' => $category,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', requirements: ['id' => '\d+'])]
    public function show(int $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);

        if (!$product || !$product->isIsActive()) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}
