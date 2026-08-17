<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Asset;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AssetService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function createAsset(Asset $asset): void
    {
        $this->entityManager->persist($asset);
        $this->entityManager->flush();
    }

    public function updateAsset(Asset $asset): void
    {
        $asset->markAsUpdated();

        $this->entityManager->flush();
    }

    public function deactivateAsset(Asset $asset): void
    {
        $asset->deactivate();
        $asset->markAsUpdated();

        $this->entityManager->flush();
    }

    public function activateAsset(Asset $asset): void
    {
        $asset->activate();
        $asset->markAsUpdated();

        $this->entityManager->flush();
    }
}
