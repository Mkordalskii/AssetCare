<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\AssetCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

final class AssetCategoryFixtures extends Fixture implements FixtureGroupInterface
{
    public const NAMES = [
        'Property', 'Vehicle', 'Bicycle', 'Computer & Electronics',
        'Home Appliance', 'HVAC', 'Tools & Equipment', 'Other',
    ];

    public static function getGroups(): array
    {
        return ['asset-categories'];
    }

    public function load(ObjectManager $manager): void
    {
        $repository = $manager->getRepository(AssetCategory::class);
        foreach (self::NAMES as $name) {
            if ($repository->findOneBy(['name' => $name]) === null) {
                $manager->persist((new AssetCategory())->setName($name));
            }
        }
        $manager->flush();
    }
}
