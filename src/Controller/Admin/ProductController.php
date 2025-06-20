<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_admin_dashboard')]
    public function dashboard(ProductRepository $productRepository, OrderRepository $orderRepository, CategoryRepository $categoryRepository): Response
    {
        $totalProducts = $productRepository->count([]);
        $activeProducts = $productRepository->count(['isActive' => true]);
        $totalOrders = $orderRepository->count([]);
        $totalCategories = $categoryRepository->count(['isActive' => true]);
        $recentOrders = $orderRepository->findBy([], ['createdAt' => 'DESC'], 5);
        $recentProducts = $productRepository->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'total_orders' => $totalOrders,
            'total_categories' => $totalCategories,
            'recent_orders' => $recentOrders,
            'recent_products' => $recentProducts,
        ]);
    }

    #[Route('/products', name: 'app_admin_products')]
    public function products(ProductRepository $productRepository, CategoryRepository $categoryRepository, Request $request): Response
    {
        $search = $request->query->get('search');
        $category = $request->query->get('category');
        $brand = $request->query->get('brand');

        $categoryEntity = null;
        if ($category) {
            $categoryEntity = $categoryRepository->findBySlug($category);
        }

        if ($search || $categoryEntity || $brand) {
            $products = $productRepository->findByFilters($search, $brand, $categoryEntity);
        } else {
            $products = $productRepository->findBy([], ['createdAt' => 'DESC']);
        }

        $brands = $productRepository->findAllBrands();
        $mainCategories = $categoryRepository->findMainCategories();

        return $this->render('admin/products/index.html.twig', [
            'products' => $products,
            'brands' => $brands,
            'mainCategories' => $mainCategories,
            'current_search' => $search,
            'current_brand' => $brand,
            'current_category' => $category,
        ]);
    }

    #[Route('/products/subcategories/{mainCategoryId}', name: 'app_admin_subcategories', methods: ['GET'])]
    public function getSubcategories(int $mainCategoryId, CategoryRepository $categoryRepository): JsonResponse
    {
        $mainCategory = $categoryRepository->find($mainCategoryId);

        if (!$mainCategory || !$mainCategory->isMainCategory()) {
            return new JsonResponse([]);
        }

        $subcategories = [];
        foreach ($mainCategory->getActiveChildren() as $subcategory) {
            $subcategories[] = [
                'id' => $subcategory->getId(),
                'name' => $subcategory->getName()
            ];
        }

        return new JsonResponse($subcategories);
    }

    #[Route('/products/new', name: 'app_admin_product_new')]
    public function newProduct(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle brand selection
            $brandChoice = $form->get('brandChoice')->getData();
            if ($brandChoice === 'new') {
                $customBrand = $form->get('brand')->getData();
                if ($customBrand) {
                    $product->setBrand($customBrand);
                }
            } else {
                $product->setBrand($brandChoice);
            }

            // Handle category selection
            $selectedCategory = $form->get('category')->getData();
            if ($selectedCategory) {
                $product->setCategory($selectedCategory);
            } else {
                // If no subcategory selected, use main category
                $mainCategory = $form->get('mainCategory')->getData();
                if ($mainCategory) {
                    $product->setCategory($mainCategory);
                }
            }

            // Handle image upload
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $uploadDir = $this->getParameter('images_directory');
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $imageFile->move($uploadDir, $newFilename);
                    $product->setImage($newFilename);

                    $this->addFlash('success', 'Image uploadée avec succès');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image: ' . $e->getMessage());
                }
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit créé avec succès');

            return $this->redirectToRoute('app_admin_products');
        }

        $existingBrands = $productRepository->findAllBrands();
        $mainCategories = $categoryRepository->findMainCategories();

        return $this->render('admin/products/new.html.twig', [
            'product' => $product,
            'form' => $form,
            'existing_brands' => $existingBrands,
            'main_categories' => $mainCategories,
        ]);
    }

    #[Route('/products/{id}/edit', name: 'app_admin_product_edit')]
    public function editProduct(Product $product, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $form = $this->createForm(ProductType::class, $product);

        // Pre-populate brand choice if product has existing brand
        if ($product->getBrand()) {
            $existingBrands = $productRepository->findAllBrands();
            if (in_array($product->getBrand(), $existingBrands)) {
                $form->get('brandChoice')->setData($product->getBrand());
            } else {
                $form->get('brandChoice')->setData('new');
                $form->get('brand')->setData($product->getBrand());
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle brand selection
            $brandChoice = $form->get('brandChoice')->getData();
            if ($brandChoice === 'new') {
                $customBrand = $form->get('brand')->getData();
                if ($customBrand) {
                    $product->setBrand($customBrand);
                }
            } else {
                $product->setBrand($brandChoice);
            }

            // Handle category selection
            $selectedCategory = $form->get('category')->getData();
            if ($selectedCategory) {
                $product->setCategory($selectedCategory);
            } else {
                // If no subcategory selected, use main category
                $mainCategory = $form->get('mainCategory')->getData();
                if ($mainCategory) {
                    $product->setCategory($mainCategory);
                }
            }

            // Handle image upload
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $product->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Produit modifié avec succès');

            return $this->redirectToRoute('app_admin_products');
        }

        $existingBrands = $productRepository->findAllBrands();
        $mainCategories = $categoryRepository->findMainCategories();

        return $this->render('admin/products/edit.html.twig', [
            'product' => $product,
            'form' => $form,
            'existing_brands' => $existingBrands,
            'main_categories' => $mainCategories,
        ]);
    }

    #[Route('/products/{id}/delete', name: 'app_admin_product_delete', methods: ['POST'])]
    public function deleteProduct(Product $product, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($product);
        $entityManager->flush();

        $this->addFlash('success', 'Produit supprimé avec succès');

        return $this->redirectToRoute('app_admin_products');
    }
}
