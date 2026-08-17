<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Manufacturer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Manufacturer>
 */
class ManufacturerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Manufacturer::class);
    }

    public function getManufacturerPaginator(
        bool $isActive,
        string $query,
        int $page,
        int $itemsPerPage,
    ): Paginator {
        $qb = $this->createQueryBuilder('manufacturer')
            ->andWhere('manufacturer.isActive = :isActive')
            ->setParameter('isActive', $isActive)
            ->orderBy('manufacturer.name', 'ASC')
            ->addOrderBy('manufacturer.id', 'ASC');

        if ($query !== '') {
            $qb
                ->andWhere('LOWER(manufacturer.name) LIKE LOWER(:query)')
                ->setParameter('query', '%' . $query . '%');
        }

        $qb
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);

        return new Paginator($qb, fetchJoinCollection: false);
    }
}
