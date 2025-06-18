<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByFilters(?string $search = null, ?string $brand = null, ?Category $category = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.createdAt', 'DESC');

        if ($search) {
            $qb->andWhere('p.name LIKE :search OR p.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($brand) {
            $qb->andWhere('p.brand = :brand')
                ->setParameter('brand', $brand);
        }

        if ($category) {
            $qb->andWhere('p.category = :category')
                ->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllBrands(): array
    {
        return $this->createQueryBuilder('p')
            ->select('DISTINCT p.brand')
            ->where('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.brand', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findFeatured(int $limit = 8): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(Category $category, ?string $search = null, ?string $brand = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->andWhere('p.category = :category')
            ->setParameter('active', true)
            ->setParameter('category', $category)
            ->orderBy('p.createdAt', 'DESC');

        if ($search) {
            $qb->andWhere('p.name LIKE :search OR p.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($brand) {
            $qb->andWhere('p.brand = :brand')
                ->setParameter('brand', $brand);
        }

        return $qb->getQuery()->getResult();
    }
}
