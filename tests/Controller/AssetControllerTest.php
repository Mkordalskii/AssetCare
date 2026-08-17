<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Asset;
use App\Entity\AssetCategory;
use App\Entity\Manufacturer;
use App\Repository\AssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AssetControllerTest extends WebTestCase
{
    public function testIndexCreateAndEditAsset(): void
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $prefix = 'asset-controller-' . bin2hex(random_bytes(4));
        $category = $this->createCategory($entityManager, $prefix . '-category');
        $manufacturer = $this->createManufacturer(
            $entityManager,
            $prefix . '-manufacturer',
        );
        $entityManager->flush();

        $client->request('GET', '/assets');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Assets');

        $crawler = $client->request('GET', '/assets/create');
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('Save asset')->form();
        $form['asset[name]'] = $prefix;
        $form['asset[category]']->select((string) $category->getId());
        $form['asset[manufacturer]']->select((string) $manufacturer->getId());
        $form['asset[model]'] = 'Model A';
        $form['asset[serialNumber]'] = 'SN-001';
        $form['asset[description]'] = 'Test asset description';
        $form['asset[purchaseDate]'] = '2026-01-10';
        $form['asset[purchasePrice]'] = '123.45';
        $form['asset[warrantyExpiresAt]'] = '2028-01-10';

        $client->submit($form);
        self::assertResponseRedirects('/assets');

        $repository = self::getContainer()->get(AssetRepository::class);
        $asset = $repository->findOneBy(['name' => $prefix]);
        self::assertNotNull($asset);
        self::assertSame($category->getId(), $asset->getCategory()?->getId());
        self::assertSame($manufacturer->getId(), $asset->getManufacturer()?->getId());
        self::assertSame('123.45', $asset->getPurchasePrice());

        $crawler = $client->request('GET', '/assets/' . $asset->getId() . '/edit');
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('Update asset')->form();
        $form['asset[model]'] = 'Model B';
        $client->submit($form);

        self::assertResponseRedirects('/assets');
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $updatedAsset = self::getContainer()
            ->get(AssetRepository::class)
            ->find($asset->getId());
        self::assertNotNull($updatedAsset);
        self::assertSame('Model B', $updatedAsset->getModel());
        self::assertNotNull($updatedAsset->getUpdatedAt());
    }

    public function testDeleteAndRestoreAssetWithCsrf(): void
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $prefix = 'asset-delete-' . bin2hex(random_bytes(4));
        $category = $this->createCategory($entityManager, $prefix . '-category');
        $asset = new Asset($prefix, $category);
        $entityManager->persist($asset);
        $entityManager->flush();
        $id = $asset->getId();

        $crawler = $client->request('GET', '/assets?q=' . $prefix);
        $deleteForm = $crawler
            ->filter('form[action="/assets/' . $id . '/delete"]')
            ->form();
        $client->submit($deleteForm);
        self::assertResponseRedirects('/assets');

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $deactivatedAsset = self::getContainer()->get(AssetRepository::class)->find($id);
        self::assertNotNull($deactivatedAsset);
        self::assertFalse($deactivatedAsset->isActive());
        self::assertNotNull($deactivatedAsset->getUpdatedAt());

        $crawler = $client->request(
            'GET',
            '/assets?status=inactive&q=' . $prefix,
        );
        $restoreForm = $crawler
            ->filter('form[action="/assets/' . $id . '/restore"]')
            ->form();
        $client->submit($restoreForm);
        self::assertResponseRedirects('/assets?status=inactive');

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $restoredAsset = self::getContainer()->get(AssetRepository::class)->find($id);
        self::assertNotNull($restoredAsset);
        self::assertTrue($restoredAsset->isActive());
        self::assertNotNull($restoredAsset->getUpdatedAt());
    }

    public function testDeleteRejectsInvalidCsrfToken(): void
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $prefix = 'asset-csrf-' . bin2hex(random_bytes(4));
        $category = $this->createCategory($entityManager, $prefix . '-category');
        $asset = new Asset($prefix, $category);
        $entityManager->persist($asset);
        $entityManager->flush();

        $client->request(
            'POST',
            '/assets/' . $asset->getId() . '/delete',
            ['_token' => 'invalid'],
        );

        self::assertResponseStatusCodeSame(403);
    }

    private function createCategory(
        EntityManagerInterface $entityManager,
        string $name,
    ): AssetCategory {
        $category = new AssetCategory();
        $category->setName($name);
        $entityManager->persist($category);

        return $category;
    }

    private function createManufacturer(
        EntityManagerInterface $entityManager,
        string $name,
    ): Manufacturer {
        $manufacturer = new Manufacturer();
        $manufacturer->setName($name);
        $entityManager->persist($manufacturer);

        return $manufacturer;
    }
}
