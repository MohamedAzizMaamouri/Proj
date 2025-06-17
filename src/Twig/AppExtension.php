<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('pad_order_number', [$this, 'padOrderNumber']),
        ];
    }

    public function padOrderNumber(int $number): string
    {
        return str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
