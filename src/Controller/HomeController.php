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
    public function index(CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        // Get all main categories
        $mainCategories = $categoryRepository->findMainCategories();

        // Get last 4 products for each main category
        $categoriesWithProducts = [];
        foreach ($mainCategories as $category) {
            $recentProducts = $productRepository->findRecentByMainCategory($category, 4);
            if (!empty($recentProducts)) {
                $categoriesWithProducts[] = [
                    'category' => $category,
                    'products' => $recentProducts
                ];
            }
        }

        // Get 3 most popular main categories (by product count)
        $popularCategories = [];
        foreach ($mainCategories as $category) {
            $productCount = $productRepository->countByMainCategory($category);
            $popularCategories[] = [
                'category' => $category,
                'productCount' => $productCount
            ];
        }

        // Sort by product count and take top 3
        usort($popularCategories, function($a, $b) {
            return $b['productCount'] - $a['productCount'];
        });
        $popularCategories = array_slice($popularCategories, 0, 3);

        return $this->render('home/index.html.twig', [
            'categoriesWithProducts' => $categoriesWithProducts,
            'popularCategories' => $popularCategories,
        ]);
    }
}
