<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Manufacturer;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

final class ManufacturerService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,) {
    }

    public function createManufacturer(Manufacturer $manufacturer): void
    {
        $this->entityManager->persist($manufacturer);
        $this->entityManager->flush();
    }
     public function updateManufacturer(Manufacturer $manufacturer): void
    {
        $manufacturer->setUpdatedAt(new DateTimeImmutable());

        $this->entityManager->flush();
    }

    public function deactivateManufacturer(Manufacturer $manufacturer): void
    {
        $manufacturer->setIsActive(false);
        $manufacturer->setUpdatedAt(new DateTimeImmutable());

        $this->entityManager->flush();
    }

    public function activateManufacturer(Manufacturer $manufacturer): void
    {
        $manufacturer->setIsActive(true);
        $manufacturer->setUpdatedAt(new DateTimeImmutable());

        $this->entityManager->flush();
    }
}