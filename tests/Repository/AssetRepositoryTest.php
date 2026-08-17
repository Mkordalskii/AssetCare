<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Asset;
use App\Entity\AssetCategory;
use App\Entity\Manufacturer;
use App\Repository\AssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AssetRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private AssetRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(
            EntityManagerInterface::class,
        );
        $this->repository = self::getContainer()->get(AssetRepository::class);
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();

        parent::tearDown();
    }

    public function testFiltersByStatusCategoryAndManufacturer(): void
    {
        $prefix = 'asset-filter-' . bin2hex(random_bytes(4));
        $categoryA = $this->createCategory($prefix . '-category-a');
        $categoryB = $this->createCategory($prefix . '-category-b');
        $manufacturerA = $this->createManufacturer($prefix . '-manufacturer-a');
        $manufacturerB = $this->createManufacturer($prefix . '-manufacturer-b');

        $matchingAsset = $this->createAsset(
            $prefix . '-matching',
            $categoryA,
            $manufacturerA,
        );
        $this->createAsset($prefix . '-other-category', $categoryB, $manufacturerA);
        $this->createAsset($prefix . '-other-manufacturer', $categoryA, $manufacturerB);
        $this->createAsset(
            $prefix . '-inactive',
            $categoryA,
            $manufacturerA,
        )->deactivate();
        $this->entityManager->flush();

        $paginator = $this->repository->getAssetPaginator(
            true,
            $categoryA,
            $manufacturerA,
            $prefix,
            1,
            10,
        );

        self::assertCount(1, $paginator);
        self::assertSame(
            $matchingAsset->getId(),
            $paginator->getIterator()->current()->getId(),
        );

        $inactivePaginator = $this->repository->getAssetPaginator(
            false,
            $categoryA,
            $manufacturerA,
            $prefix,
            1,
            10,
        );

        self::assertCount(1, $inactivePaginator);
        self::assertSame(
            $prefix . '-inactive',
            $inactivePaginator->getIterator()->current()->getName(),
        );
    }

    public function testSearchesByNameModelAndSerialNumber(): void
    {
        $prefix = 'asset-search-' . bin2hex(random_bytes(4));
        $category = $this->createCategory($prefix . '-category');

        $this->createAsset($prefix . '-needle-name', $category);
        $this->createAsset($prefix . '-model', $category)
            ->setModel($prefix . '-needle-model');
        $this->createAsset($prefix . '-serial', $category)
            ->setSerialNumber($prefix . '-needle-serial');
        $this->createAsset($prefix . '-unrelated', $category);
        $this->entityManager->flush();

        $paginator = $this->repository->getAssetPaginator(
            true,
            null,
            null,
            $prefix . '-needle',
            1,
            10,
        );

        self::assertCount(3, $paginator);
        self::assertCount(3, iterator_to_array($paginator));
    }

    public function testPaginatesResultsWithStableOrdering(): void
    {
        $prefix = 'asset-page-' . bin2hex(random_bytes(4));
        $category = $this->createCategory($prefix . '-category');

        for ($number = 1; $number <= 11; ++$number) {
            $this->createAsset(
                sprintf('%s-%02d', $prefix, $number),
                $category,
            );
        }
        $this->entityManager->flush();

        $paginator = $this->repository->getAssetPaginator(
            true,
            $category,
            null,
            $prefix,
            2,
            10,
        );
        $items = iterator_to_array($paginator);

        self::assertCount(11, $paginator);
        self::assertCount(1, $items);
        self::assertSame($prefix . '-11', $items[0]->getName());
    }

    private function createCategory(string $name): AssetCategory
    {
        $category = new AssetCategory();
        $category->setName($name);
        $this->entityManager->persist($category);

        return $category;
    }

    private function createManufacturer(string $name): Manufacturer
    {
        $manufacturer = new Manufacturer();
        $manufacturer->setName($name);
        $this->entityManager->persist($manufacturer);

        return $manufacturer;
    }

    private function createAsset(
        string $name,
        AssetCategory $category,
        ?Manufacturer $manufacturer = null,
    ): Asset {
        $asset = new Asset($name, $category);
        $asset->setManufacturer($manufacturer);
        $this->entityManager->persist($asset);

        return $asset;
    }
}
