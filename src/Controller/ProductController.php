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
        $subcategorySlug = $request->query->get('subcategory');
        $sort = $request->query->get('sort');

        $category = null;
        $subcategory = null;
        $currentMainCategory = null;
        $availableSubcategories = [];
        $filterCategory = null;

        // Debug: Let's see what we're working with
        if ($this->getParameter('kernel.environment') === 'dev') {
            dump([
                'categorySlug' => $categorySlug,
                'subcategorySlug' => $subcategorySlug,
            ]);
        }

        // Handle category filtering
        if ($categorySlug) {
            $category = $categoryRepository->findBySlug($categorySlug);

            if ($this->getParameter('kernel.environment') === 'dev') {
                dump([
                    'found_category' => $category ? $category->getName() : 'null',
                    'is_main_category' => $category ? $category->isMainCategory() : 'null',
                    'parent' => $category && $category->getParent() ? $category->getParent()->getName() : 'null'
                ]);
            }

            if ($category) {
                if ($category->isMainCategory()) {
                    // This is a main category
                    $currentMainCategory = $category;
                    $availableSubcategories = $category->getActiveChildren();
                    $filterCategory = $category;
                } else {
                    // This might be a subcategory passed as category parameter
                    $parent = $category->getParent();
                    if ($parent && $parent->isMainCategory()) {
                        $currentMainCategory = $parent;
                        $availableSubcategories = $parent->getActiveChildren();

                        // If no subcategory is explicitly set, but the category is actually a subcategory,
                        // treat it as if the subcategory was selected
                        if (!$subcategorySlug) {
                            $subcategory = $category;
                            $filterCategory = $category;
                        } else {
                            $filterCategory = $category;
                        }
                    }
                }
            }
        }

        // Handle explicit subcategory filtering
        if ($subcategorySlug && $currentMainCategory) {
            $subcategory = $categoryRepository->findBySlug($subcategorySlug);
            if ($subcategory && $subcategory->getParent() === $currentMainCategory) {
                $filterCategory = $subcategory;
            }
        }

        // Get products based on the determined filter category
        $products = $productRepository->findByFilters($search, $brand, $filterCategory, $sort);

        // Get brands available for the current filter context
        $brands = $productRepository->findAllBrands($filterCategory);

        // Get all main categories for navigation
        $mainCategories = $categoryRepository->findMainCategories();

        // Add product counts to main categories
        foreach ($mainCategories as $mainCategory) {
            $mainCategory->productCount = $productRepository->countByCategory($mainCategory);
        }

        // Add product counts to subcategories
        foreach ($availableSubcategories as $subcat) {
            $subcat->productCount = $productRepository->countByCategory($subcat);
        }

        // Calculate counts for display in sidebar
        $mainCategoryDisplayCount = 0;
        if ($currentMainCategory) {
            if ($subcategory) {
                $mainCategoryDisplayCount = $productRepository->countByCategory($currentMainCategory);
            } else {
                $mainCategoryDisplayCount = count($products);
            }
        }

        if ($this->getParameter('kernel.environment') === 'dev') {
            dump([
                'final_current_main_category' => $currentMainCategory ? $currentMainCategory->getName() : 'null',
                'final_current_subcategory' => $subcategory ? $subcategory->getName() : 'null',
                'filter_category' => $filterCategory ? $filterCategory->getName() : 'null',
                'products_count' => count($products)
            ]);
        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'brands' => $brands,
            'mainCategories' => $mainCategories,
            'currentMainCategory' => $currentMainCategory,
            'availableSubcategories' => $availableSubcategories,
            'mainCategoryDisplayCount' => $mainCategoryDisplayCount,
            'current_search' => $search,
            'current_brand' => $brand,
            'current_category' => $currentMainCategory,
            'current_subcategory' => $subcategory,
            'current_sort' => $sort,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', requirements: ['id' => '\d+'])]
    public function show(int $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);

        if (!$product || !$product->isIsActive()) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $relatedProducts = $productRepository->findRelatedProducts($product, 4);

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    #[Route('/search', name: 'app_product_search', methods: ['GET'])]
    public function search(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $query = $request->query->get('q', '');
        $products = [];
        $brands = [];

        if (strlen(trim($query)) >= 2) {
            // Use the existing findByFilters method with search parameter
            $products = $productRepository->findByFilters($query, null, null, null);
            $brands = $productRepository->findAllBrands();
        }

        // Get all main categories for navigation
        $mainCategories = $categoryRepository->findMainCategories();

        return $this->render('product/search.html.twig', [
            'products' => $products,
            'query' => $query,
            'total' => count($products),
            'brands' => $brands,
            'mainCategories' => $mainCategories,
        ]);
    }
}
