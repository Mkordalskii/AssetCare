<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\ManufacturerPage;
use App\Repository\ManufacturerRepository;

final readonly class ManufacturerListService
{
    public const ITEMS_PER_PAGE = 10;

    public function __construct(
        private ManufacturerRepository $manufacturerRepository,
    ) {
    }

    public function getPage(
        bool $isActive,
        string $query,
        int $requestedPage,
    ): ManufacturerPage {
        $requestedPage = max(1, $requestedPage);
        $paginator = $this->manufacturerRepository->getManufacturerPaginator(
            $isActive,
            $query,
            $requestedPage,
            self::ITEMS_PER_PAGE,
        );

        $totalItems = count($paginator);
        $totalPages = max(
            1,
            (int) ceil($totalItems / self::ITEMS_PER_PAGE),
        );
        $currentPage = min($requestedPage, $totalPages);

        if ($currentPage !== $requestedPage) {
            $paginator = $this->manufacturerRepository->getManufacturerPaginator(
                $isActive,
                $query,
                $currentPage,
                self::ITEMS_PER_PAGE,
            );
        }

        return new ManufacturerPage(
            $paginator,
            $totalItems,
            $currentPage,
            $totalPages,
        );
    }
}
