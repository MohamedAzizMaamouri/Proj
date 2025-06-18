<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/users')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_admin_users', methods: ['GET'])]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        $role = $request->query->get('role');

        $users = $userRepository->findPaginatedUsers($page, $limit, $role);
        $totalUsers = $userRepository->countUsers($role);
        $totalPages = ceil($totalUsers / $limit);

        $roleCounts = [
            'all' => $userRepository->countUsers(),
            'admin' => $userRepository->countByRole('ROLE_ADMIN'),
            'user' => $userRepository->countByRole('ROLE_USER'),
        ];

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_users' => $totalUsers,
            'current_role' => $role,
            'role_counts' => $roleCounts,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $firstName = $request->request->get('firstName');
            $lastName = $request->request->get('lastName');
            $password = $request->request->get('password');
            $role = $request->request->get('role', 'ROLE_USER');

            // Vérifier si l'email existe déjà
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                $this->addFlash('error', 'Un utilisateur avec cet email existe déjà.');
                return $this->render('admin/user/new.html.twig');
            }

            $user = new User();
            $user->setEmail($email);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setRoles([$role]);

            // Hash du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/user/new.html.twig');
    }

    #[Route('/{id}/promote', name: 'app_admin_user_promote', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function promote(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('promote'.$user->getId(), $request->request->get('_token'))) {
            $newRole = $request->request->get('role');

            if (in_array($newRole, ['ROLE_USER', 'ROLE_ADMIN'])) {
                $user->setRoles([$newRole]);
                $entityManager->flush();

                $roleText = $newRole === 'ROLE_ADMIN' ? 'administrateur' : 'utilisateur';
                $this->addFlash('success', "L'utilisateur a été promu {$roleText} avec succès.");
            } else {
                $this->addFlash('error', 'Rôle invalide.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()]);
    }
}
