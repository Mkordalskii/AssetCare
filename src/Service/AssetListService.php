<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\PaginatedPage;
use App\Entity\AssetCategory;
use App\Entity\Manufacturer;
use App\Repository\AssetCategoryRepository;
use App\Repository\AssetRepository;
use App\Repository\ManufacturerRepository;

final readonly class AssetListService
{
    public const ITEMS_PER_PAGE = 10;

    public function __construct(
        private AssetRepository $assetRepository,
        private AssetCategoryRepository $assetCategoryRepository,
        private ManufacturerRepository $manufacturerRepository,
    ) {
    }

    public function getPage(
        bool $isActive,
        string $query,
        ?int $categoryId,
        ?int $manufacturerId,
        int $requestedPage,
    ): PaginatedPage {
        $category = $categoryId === null
            ? null
            : $this->assetCategoryRepository->find($categoryId);
        $manufacturer = $manufacturerId === null
            ? null
            : $this->manufacturerRepository->find($manufacturerId);
        $requestedPage = max(1, $requestedPage);

        $paginator = $this->assetRepository->getAssetPaginator(
            $isActive,
            $category,
            $manufacturer,
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
            $paginator = $this->assetRepository->getAssetPaginator(
                $isActive,
                $category,
                $manufacturer,
                $query,
                $currentPage,
                self::ITEMS_PER_PAGE,
            );
        }

        return new PaginatedPage(
            $paginator,
            $totalItems,
            $currentPage,
            $totalPages,
        );
    }

    /** @return AssetCategory[] */
    public function getCategories(): array
    {
        return $this->assetCategoryRepository->findBy(
            ['isActive' => true],
            ['name' => 'ASC'],
        );
    }

    /** @return Manufacturer[] */
    public function getManufacturers(): array
    {
        return $this->manufacturerRepository->findBy(
            ['isActive' => true],
            ['name' => 'ASC'],
        );
    }
}
