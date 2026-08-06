<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Manufacturer;
use Doctrine\ORM\EntityManagerInterface;

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
}