<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Asset;
use App\Entity\AssetCategory;
use App\Entity\Manufacturer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Asset>
 */
final class AssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asset::class);
    }

    public function getAssetPaginator(
        bool $isActive,
        ?AssetCategory $category,
        ?Manufacturer $manufacturer,
        string $query,
        int $page,
        int $itemsPerPage,
    ): Paginator {
        $qb = $this->createQueryBuilder('asset')
            ->andWhere('asset.isActive = :isActive')
            ->setParameter('isActive', $isActive)
            ->orderBy('asset.name', 'ASC')
            ->addOrderBy('asset.id', 'ASC');

        if ($category !== null) {
            $qb
                ->andWhere('asset.category = :category')
                ->setParameter('category', $category);
        }

        if ($manufacturer !== null) {
            $qb
                ->andWhere('asset.manufacturer = :manufacturer')
                ->setParameter('manufacturer', $manufacturer);
        }

        if ($query !== '') {
            $qb
                ->andWhere($qb->expr()->orX(
                    'LOWER(asset.name) LIKE LOWER(:query)',
                    'LOWER(asset.model) LIKE LOWER(:query)',
                    'LOWER(asset.serialNumber) LIKE LOWER(:query)',
                ))
                ->setParameter('query', '%' . $query . '%');
        }

        $qb
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);

        return new Paginator($qb, fetchJoinCollection: false);
    }
}
