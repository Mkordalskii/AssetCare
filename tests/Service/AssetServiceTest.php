<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Asset;
use App\Entity\AssetCategory;
use App\Service\AssetService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AssetServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private AssetService $service;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->service = new AssetService($this->entityManager);
    }

    public function testCreatesAsset(): void
    {
        $asset = $this->createAsset();

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($asset);
        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->service->createAsset($asset);

        self::assertNull($asset->getUpdatedAt());
    }

    public function testUpdatesAssetAndTimestamp(): void
    {
        $asset = $this->createAsset();

        $this->entityManager
            ->expects(self::never())
            ->method('persist');
        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->service->updateAsset($asset);

        self::assertNotNull($asset->getUpdatedAt());
    }

    public function testDeactivatesAssetAndUpdatesTimestamp(): void
    {
        $asset = $this->createAsset();

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->service->deactivateAsset($asset);

        self::assertFalse($asset->isActive());
        self::assertNotNull($asset->getUpdatedAt());
    }

    public function testActivatesAssetAndUpdatesTimestamp(): void
    {
        $asset = $this->createAsset();
        $asset->deactivate();

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->service->activateAsset($asset);

        self::assertTrue($asset->isActive());
        self::assertNotNull($asset->getUpdatedAt());
    }

    private function createAsset(): Asset
    {
        $category = new AssetCategory();
        $category->setName('Test category');

        return new Asset('Test asset', $category);
    }
}
