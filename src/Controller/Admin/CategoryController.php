<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Form\SubcategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/categories')]
#[IsGranted('ROLE_ADMIN')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'app_admin_categories')]
    public function index(CategoryRepository $categoryRepository, Request $request): Response
    {
        $search = $request->query->get('search');
        $status = $request->query->get('status');
        $mainCategoryId = $request->query->get('main_category');
        $mainStatus = $request->query->get('main_status', 'active'); // New filter for main categories

        // Get main categories based on status filter
        if ($mainStatus === 'all') {
            $mainCategories = $categoryRepository->findBy(['isMainCategory' => true], ['sortOrder' => 'ASC', 'name' => 'ASC']);
        } elseif ($mainStatus === 'inactive') {
            $mainCategories = $categoryRepository->findBy(['isMainCategory' => true, 'isActive' => false], ['sortOrder' => 'ASC', 'name' => 'ASC']);
        } else {
            $mainCategories = $categoryRepository->findMainCategories(); // Active only
        }

        // Get subcategories (only from active main categories for the dropdown)
        $activeMainCategories = $categoryRepository->findMainCategories();
        $subcategories = [];
        foreach ($activeMainCategories as $mainCategory) {
            foreach ($mainCategory->getChildren() as $subcategory) {
                $subcategories[] = $subcategory;
            }
        }

        // Apply filters to subcategories
        if ($search || $status || $mainCategoryId) {
            $filteredSubcategories = [];
            foreach ($subcategories as $category) {
                if ($this->matchesFilters($category, $search, $status, $mainCategoryId)) {
                    $filteredSubcategories[] = $category;
                }
            }
            $subcategories = $filteredSubcategories;
        }

        return $this->render('admin/categories/index.html.twig', [
            'mainCategories' => $mainCategories,
            'activeMainCategories' => $activeMainCategories, // For the filter dropdown
            'subcategories' => $subcategories,
            'current_search' => $search,
            'current_status' => $status,
            'current_main_category' => $mainCategoryId,
            'current_main_status' => $mainStatus,
        ]);
    }

    private function matchesFilters(Category $category, ?string $search, ?string $status, ?string $mainCategoryId): bool
    {
        // Search filter
        if ($search) {
            $searchLower = strtolower($search);
            if (strpos(strtolower($category->getName()), $searchLower) === false &&
                strpos(strtolower($category->getDescription() ?? ''), $searchLower) === false) {
                return false;
            }
        }

        // Status filter
        if ($status) {
            if ($status === 'active' && !$category->isIsActive()) {
                return false;
            }
            if ($status === 'inactive' && $category->isIsActive()) {
                return false;
            }
        }

        // Main category filter
        if ($mainCategoryId) {
            if (!$category->getParent() || $category->getParent()->getId() != $mainCategoryId) {
                return false;
            }
        }

        return true;
    }

    #[Route('/main-category/new', name: 'app_admin_main_category_new')]
    public function newMainCategory(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $category = new Category();
        $category->setIsMainCategory(true);
        $category->setIsActive(true);

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Generate slug from name
            $slug = $slugger->slug($category->getName())->lower();
            $category->setSlug($slug);

            // Handle image upload
            $this->handleImageUpload($form, $category, $slugger);

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie principale créée avec succès');

            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/categories/new-main.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/subcategory/new', name: 'app_admin_subcategory_new')]
    public function newSubcategory(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $category = new Category();
        $category->setIsMainCategory(false);

        $form = $this->createForm(SubcategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Generate slug from name
            $slug = $slugger->slug($category->getName())->lower();
            $category->setSlug($slug);

            // Handle image upload
            $this->handleImageUpload($form, $category, $slugger);

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Sous-catégorie créée avec succès');

            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/categories/new-sub.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_category_edit')]
    public function edit(Category $category, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        if ($category->isMainCategory()) {
            $form = $this->createForm(CategoryType::class, $category);
            $template = 'admin/categories/edit-main.html.twig';
        } else {
            $form = $this->createForm(SubcategoryType::class, $category);
            $template = 'admin/categories/edit-sub.html.twig';
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update slug if name changed
            $slug = $slugger->slug($category->getName())->lower();
            $category->setSlug($slug);

            // Handle image upload
            $this->handleImageUpload($form, $category, $slugger);

            $entityManager->flush();

            $categoryType = $category->isMainCategory() ? 'Catégorie principale' : 'Sous-catégorie';
            $this->addFlash('success', $categoryType . ' modifiée avec succès');

            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render($template, [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_category_delete', methods: ['POST'])]
    public function delete(Category $category, EntityManagerInterface $entityManager): Response
    {
        // Check if main category has subcategories
        if ($category->isMainCategory() && $category->getChildren()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer cette catégorie principale car elle contient des sous-catégories. Veuillez d\'abord supprimer ou déplacer les sous-catégories.');
            return $this->redirectToRoute('app_admin_categories');
        }

        // Check if category has products
        if ($category->getProducts()->count() > 0) {
            $categoryType = $category->isMainCategory() ? 'catégorie principale' : 'sous-catégorie';
            $this->addFlash('error', "Impossible de supprimer cette {$categoryType} car elle contient des produits. Veuillez d'abord déplacer ou supprimer les produits associés.");
            return $this->redirectToRoute('app_admin_categories');
        }

        $categoryType = $category->isMainCategory() ? 'Catégorie principale' : 'Sous-catégorie';
        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash('success', $categoryType . ' supprimée avec succès');

        return $this->redirectToRoute('app_admin_categories');
    }

    #[Route('/{id}/toggle-status', name: 'app_admin_category_toggle_status', methods: ['POST'])]
    public function toggleStatus(Category $category, EntityManagerInterface $entityManager): Response
    {
        $category->setIsActive(!$category->isIsActive());
        $entityManager->flush();

        $categoryType = $category->isMainCategory() ? 'Catégorie principale' : 'Sous-catégorie';
        $status = $category->isIsActive() ? 'activée' : 'désactivée';
        $this->addFlash('success', "{$categoryType} {$status} avec succès");

        return $this->redirectToRoute('app_admin_categories');
    }

    private function handleImageUpload($form, Category $category, SluggerInterface $slugger): void
    {
        $imageFile = $form->get('imageFile')->getData();
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $uploadDir = $this->getParameter('categories_directory') ?? 'public/uploads/categories';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $imageFile->move($uploadDir, $newFilename);
                $category->setImage($newFilename);

                $this->addFlash('success', 'Image uploadée avec succès');
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur lors de l\'upload de l\'image: ' . $e->getMessage());
            }
        }
    }
}
