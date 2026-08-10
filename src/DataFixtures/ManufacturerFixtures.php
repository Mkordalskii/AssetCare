<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Manufacturer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ManufacturerFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $manufacturer = new Manufacturer();

            $manufacturer
                ->setName('Manufacturer ' . $i)
                ->setWebsite('https://example' . $i . '.com')
                ->setSupportEmail('support' . $i . '@example.com');

            $manager->persist($manufacturer);
        }

        $manager->flush();
    }
}
