<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findPaginatedUsers(int $page, int $limit, ?string $role = null): array
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC');

        if ($role) {
            if ($role === 'ROLE_ADMIN') {
                // Chercher les utilisateurs qui ont ROLE_ADMIN
                $qb->andWhere('u.roles LIKE :role')
                    ->setParameter('role', '%"ROLE_ADMIN"%');
            } elseif ($role === 'ROLE_USER') {
                // Chercher les utilisateurs qui n'ont PAS ROLE_ADMIN (donc seulement ROLE_USER)
                $qb->andWhere('u.roles NOT LIKE :admin_role')
                    ->setParameter('admin_role', '%"ROLE_ADMIN"%');
            }
        }

        return $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countUsers(?string $role = null): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)');

        if ($role) {
            if ($role === 'ROLE_ADMIN') {
                // Compter les utilisateurs qui ont ROLE_ADMIN
                $qb->andWhere('u.roles LIKE :role')
                    ->setParameter('role', '%"ROLE_ADMIN"%');
            } elseif ($role === 'ROLE_USER') {
                // Compter les utilisateurs qui n'ont PAS ROLE_ADMIN
                $qb->andWhere('u.roles NOT LIKE :admin_role')
                    ->setParameter('admin_role', '%"ROLE_ADMIN"%');
            }
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function countByRole(string $role): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)');

        if ($role === 'ROLE_ADMIN') {
            // Compter les utilisateurs qui ont ROLE_ADMIN
            $qb->andWhere('u.roles LIKE :role')
                ->setParameter('role', '%"ROLE_ADMIN"%');
        } elseif ($role === 'ROLE_USER') {
            // Compter les utilisateurs qui n'ont PAS ROLE_ADMIN
            $qb->andWhere('u.roles NOT LIKE :admin_role')
                ->setParameter('admin_role', '%"ROLE_ADMIN"%');
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findRecentUsers(int $limit = 5): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
