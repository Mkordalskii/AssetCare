<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Manufacturer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    /**
     * @return Manufacturer[] Returns an array of Manufacturer objects
     */
    public function findByFilters(
        bool $isActive,
        ?string $query = null
    ): array {
        $qb = $this->createQueryBuilder('manufacturer')
            ->andWhere('manufacturer.isActive = :isActive')
            ->setParameter('isActive', $isActive)
            ->orderBy('manufacturer.name', 'ASC');

        if ($query !== null && $query !== '') {
            $qb
                ->andWhere('LOWER(manufacturer.name) LIKE LOWER(:query)')
                ->setParameter('query', '%' . $query . '%');
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
    // public function findAllActive(): array
    // {
    //     return $this->createQueryBuilder('manufacturer')
    //         ->andWhere('manufacturer.isActive = :isActive')
    //         ->setParameter('isActive', true)
    //         ->orderBy('manufacturer.name', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }
    // public function findAllInactive(): array
    // {
    //     return $this->createQueryBuilder('manufacturer')
    //         ->andWhere('manufacturer.isActive = :isActive')
    //         ->setParameter('isActive', false)
    //         ->orderBy('manufacturer.name', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }

    //    /**
    //     * @return Manufacturer[] Returns an array of Manufacturer objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Manufacturer
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
