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

    public function findByFilters(?string $search = null, ?string $brand = null, ?Category $category = null, ?string $priceOrder = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->where('p.isActive = :active')
            ->setParameter('active', true);

        if ($search) {
            $qb->andWhere('p.name LIKE :search OR p.description LIKE :search OR p.brand LIKE :search OR p.model LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($brand) {
            $qb->andWhere('p.brand = :brand')
                ->setParameter('brand', $brand);
        }

        if ($category) {
            if ($category->isMainCategory()) {
                // For main categories, include products from the main category and all its subcategories
                $categoryIds = $this->getCategoryAndChildrenIds($category);
            } else {
                // For subcategories, only include products from that specific subcategory
                $categoryIds = [$category->getId()];
            }

            $qb->andWhere('p.category IN (:categories)')
                ->setParameter('categories', $categoryIds);
        }

        // Add sorting
        switch ($priceOrder) {
            case 'price_asc':
                $qb->orderBy('p.price', 'ASC');
                break;
            case 'price_desc':
                $qb->orderBy('p.price', 'DESC');
                break;
            case 'name_asc':
                $qb->orderBy('p.name', 'ASC');
                break;
            case 'name_desc':
                $qb->orderBy('p.name', 'DESC');
                break;
            default:
                $qb->orderBy('p.createdAt', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    public function findByMainCategory(Category $mainCategory): array
    {
        if (!$mainCategory->isMainCategory()) {
            return [];
        }

        $categoryIds = $this->getCategoryAndChildrenIds($mainCategory);

        return $this->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->andWhere('p.category IN (:categories)')
            ->setParameter('active', true)
            ->setParameter('categories', $categoryIds)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(Category $category, ?string $search = null, ?string $brand = null, ?string $priceOrder = null): array
    {
        return $this->findByFilters($search, $brand, $category, $priceOrder);
    }

    /**
     * Get all category IDs including the category itself and all its children recursively
     */
    private function getCategoryAndChildrenIds(Category $category): array
    {
        $categoryIds = [$category->getId()];

        foreach ($category->getChildren() as $child) {
            if ($child->isIsActive()) {
                $categoryIds = array_merge($categoryIds, $this->getCategoryAndChildrenIds($child));
            }
        }

        return $categoryIds;
    }

    public function findAllBrands(?Category $category = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('DISTINCT p.brand')
            ->where('p.isActive = :active')
            ->setParameter('active', true);

        if ($category) {
            if ($category->isMainCategory()) {
                $categoryIds = $this->getCategoryAndChildrenIds($category);
            } else {
                $categoryIds = [$category->getId()];
            }

            $qb->andWhere('p.category IN (:categories)')
                ->setParameter('categories', $categoryIds);
        }

        return $qb->orderBy('p.brand', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function countByMainCategory(Category $mainCategory): int
    {
        if (!$mainCategory->isMainCategory()) {
            return 0;
        }

        $categoryIds = $this->getCategoryAndChildrenIds($mainCategory);

        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.isActive = :active')
            ->andWhere('p.category IN (:categories)')
            ->setParameter('active', true)
            ->setParameter('categories', $categoryIds)
            ->getQuery()
            ->getSingleScalarResult();
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

    public function findByCategoryWithPagination(Category $category, int $page = 1, int $limit = 12): array
    {
        $categoryIds = $this->getCategoryAndChildrenIds($category);

        $qb = $this->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->andWhere('p.category IN (:categories)')
            ->setParameter('active', true)
            ->setParameter('categories', $categoryIds)
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countByCategory(Category $category): int
    {
        $categoryIds = $this->getCategoryAndChildrenIds($category);

        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.isActive = :active')
            ->andWhere('p.category IN (:categories)')
            ->setParameter('active', true)
            ->setParameter('categories', $categoryIds)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRelatedProducts(Product $product, int $limit = 4): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->andWhere('p.id != :currentProduct')
            ->setParameter('active', true)
            ->setParameter('currentProduct', $product->getId())
            ->setMaxResults($limit);

        // First try to find products in the same category
        if ($product->getCategory()) {
            $categoryIds = $this->getCategoryAndChildrenIds($product->getCategory());
            $qb->andWhere('p.category IN (:categories)')
                ->setParameter('categories', $categoryIds);
        }

        $results = $qb->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // If not enough results, get products from the same brand
        if (count($results) < $limit) {
            $remaining = $limit - count($results);
            $brandProducts = $this->createQueryBuilder('p')
                ->where('p.isActive = :active')
                ->andWhere('p.id != :currentProduct')
                ->andWhere('p.brand = :brand')
                ->setParameter('active', true)
                ->setParameter('currentProduct', $product->getId())
                ->setParameter('brand', $product->getBrand())
                ->setMaxResults($remaining)
                ->orderBy('p.createdAt', 'DESC')
                ->getQuery()
                ->getResult();

            $results = array_merge($results, $brandProducts);
        }

        return $results;
    }

    public function findRecentByMainCategory(Category $mainCategory, int $limit = 4): array
    {
        if (!$mainCategory->isMainCategory()) {
            return [];
        }

        $categoryIds = $this->getCategoryAndChildrenIds($mainCategory);

        return $this->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->andWhere('p.category IN (:categories)')
            ->setParameter('active', true)
            ->setParameter('categories', $categoryIds)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
