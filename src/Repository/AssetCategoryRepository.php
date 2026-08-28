<?php
declare(strict_types=1);
namespace App\Repository;

use App\Entity\AssetCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AssetCategory>
 */
class AssetCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssetCategory::class);
    }

    /** @return AssetCategory[] */
    public function search(bool $isActive, string $query): array
    {
        $builder = $this->createQueryBuilder('category')
            ->andWhere('category.isActive = :active')
            ->setParameter('active', $isActive)
            ->orderBy('category.name', 'ASC');

        if ($query !== '') {
            $builder->andWhere('LOWER(category.name) LIKE LOWER(:query)')
                ->setParameter('query', '%' . $query . '%');
        }

        return $builder->getQuery()->getResult();
    }
}
