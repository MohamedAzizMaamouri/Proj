<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categories')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'app_category_index')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        // Show ALL active categories, not just root categories
        $categories = $categoryRepository->findAllActive();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/{slug}', name: 'app_category_show')]
    public function show(string $slug, CategoryRepository $categoryRepository, ProductRepository $productRepository, Request $request): Response
    {
        $category = $categoryRepository->findBySlug($slug);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        $search = $request->query->get('search');
        $brand = $request->query->get('brand');

        $products = $productRepository->findByCategory($category, $search, $brand);
        $brands = $productRepository->findAllBrands();

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'products' => $products,
            'brands' => $brands,
            'current_search' => $search,
            'current_brand' => $brand,
        ]);
    }
}
