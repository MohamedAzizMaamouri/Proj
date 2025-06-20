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
        $mainCategories = $categoryRepository->findMainCategories();

        return $this->render('category/index.html.twig', [
            'mainCategories' => $mainCategories,
        ]);
    }

    #[Route('/{slug}', name: 'app_category_show')]
    public function show(string $slug, CategoryRepository $categoryRepository, ProductRepository $productRepository, Request $request): Response
    {
        $category = $categoryRepository->findBySlug($slug);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        // Redirect to product index with category filter
        $queryParams = array_merge($request->query->all(), ['category' => $slug]);

        return $this->redirectToRoute('app_product_index', $queryParams);
    }
}
