<?php

declare(strict_types=1);

namespace App\Dto;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class ManufacturerPage
{
    public function __construct(
        public Paginator $items,
        public int $totalItems,
        public int $currentPage,
        public int $totalPages,
    ) {
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages;
    }
}
