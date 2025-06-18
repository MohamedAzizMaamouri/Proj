<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        // Get featured products (latest 8)
        $products = $productRepository->findFeatured(8);

        // Get the 3 most recent categories (or main categories)
        $categories = $categoryRepository->findRootCategories();

        return $this->render('home/index.html.twig', [
            'products' => $products,
            'categories' => array_slice($categories, 0, 3), // Get first 3 categories
        ]);
    }
}
