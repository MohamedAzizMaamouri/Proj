<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
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

        if ($search || $status) {
            $categories = $categoryRepository->findByFilters($search, $status);
        } else {
            $categories = $categoryRepository->findBy([], ['createdAt' => 'DESC']);
        }

        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categories,
            'current_search' => $search,
            'current_status' => $status,
        ]);
    }

    #[Route('/new', name: 'app_admin_category_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Generate slug from name
            $slug = $slugger->slug($category->getName())->lower();
            $category->setSlug($slug);

            // Handle image upload
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

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie créée avec succès');

            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/categories/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_category_edit')]
    public function edit(Category $category, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update slug if name changed
            $slug = $slugger->slug($category->getName())->lower();
            $category->setSlug($slug);

            // Handle image upload
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $uploadDir = $this->getParameter('categories_directory') ?? 'public/uploads/categories';
                    $imageFile->move($uploadDir, $newFilename);
                    $category->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Catégorie modifiée avec succès');

            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/categories/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_category_delete', methods: ['POST'])]
    public function delete(Category $category, EntityManagerInterface $entityManager): Response
    {
        // Check if category has products
        if ($category->getProducts()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer cette catégorie car elle contient des produits. Veuillez d\'abord déplacer ou supprimer les produits associés.');
            return $this->redirectToRoute('app_admin_categories');
        }

        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash('success', 'Catégorie supprimée avec succès');

        return $this->redirectToRoute('app_admin_categories');
    }

    #[Route('/{id}/toggle-status', name: 'app_admin_category_toggle_status', methods: ['POST'])]
    public function toggleStatus(Category $category, EntityManagerInterface $entityManager): Response
    {
        $category->setIsActive(!$category->isIsActive());
        $entityManager->flush();

        $status = $category->isIsActive() ? 'activée' : 'désactivée';
        $this->addFlash('success', "Catégorie {$status} avec succès");

        return $this->redirectToRoute('app_admin_categories');
    }
}
