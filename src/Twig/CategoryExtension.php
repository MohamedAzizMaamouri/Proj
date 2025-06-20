<?php

namespace App\Twig;

use App\Repository\CategoryRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CategoryExtension extends AbstractExtension
{
    public function __construct(
        private CategoryRepository $categoryRepository
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_main_categories', [$this, 'getMainCategories']),
        ];
    }

    public function getMainCategories(): array
    {
        return $this->categoryRepository->findMainCategories();
    }
}
