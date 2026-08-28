<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AssetCategoryFixtures;
use App\Entity\AssetCategory;
use App\Repository\AssetCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AssetCategoryControllerTest extends WebTestCase
{
    public function testListSearchAndStatus(): void
    {
        $client = static::createClient();
        $prefix = 'Category-' . bin2hex(random_bytes(4));
        $this->createCategory($prefix . ' Alpha');
        $this->createCategory($prefix . ' Beta', false);

        $client->request('GET', '/categories', ['q' => '  ' . strtolower($prefix) . '  ']);
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'tbody tr');
        self::assertSelectorTextContains('tbody', $prefix . ' Alpha');
        self::assertSelectorCount(1, 'nav a[aria-current="page"]');
        self::assertSelectorTextContains('nav a[aria-current="page"]', 'Categories');

        $client->request('GET', '/categories', ['q' => $prefix, 'status' => 'inactive']);
        self::assertSelectorCount(1, 'tbody tr');
        self::assertSelectorTextContains('tbody', $prefix . ' Beta');

        $client->request('GET', '/categories', ['q' => $prefix . '-missing']);
        self::assertSelectorTextContains('.alert-info', 'No categories found.');
    }

    public function testCreateAndEdit(): void
    {
        $client = static::createClient();
        $name = 'Created-' . bin2hex(random_bytes(4));
        $client->request('GET', '/categories/create');
        self::assertResponseIsSuccessful();
        $client->submitForm('Save category', [
            'asset_category[name]' => $name,
            'asset_category[description]' => 'Original description',
            'asset_category[icon]' => 'house',
        ]);
        self::assertResponseRedirects('/categories');
        $category = static::getContainer()->get(AssetCategoryRepository::class)->findOneBy(['name' => $name]);
        self::assertNotNull($category);
        self::assertTrue($category->isActive());
        self::assertSame('house', $category->getIcon());
        self::assertSame('Original description', $category->getDescription());
        $id = $category->getId();
        $createdAt = $category->getCreatedAt()->format('Y-m-d H:i:s');

        $client->request('GET', '/categories/' . $id . '/edit');
        self::assertResponseIsSuccessful();
        $client->submitForm('Save category', [
            'asset_category[name]' => $name . ' edited',
            'asset_category[description]' => '',
            'asset_category[icon]' => '',
        ]);
        self::assertResponseRedirects('/categories');
        $category = $this->reload($id);
        self::assertSame($name . ' edited', $category->getName());
        self::assertNull($category->getDescription());
        self::assertNull($category->getIcon());
        self::assertNotNull($category->getUpdatedAt());
        self::assertSame($createdAt, $category->getCreatedAt()->format('Y-m-d H:i:s'));
    }

    public function testInvalidNamesAreRejected(): void
    {
        $client = static::createClient();
        foreach (['   ', str_repeat('x', 101)] as $name) {
            $client->request('GET', '/categories/create');
            $client->submitForm('Save category', ['asset_category[name]' => $name]);
            self::assertSelectorExists('.invalid-feedback');
            self::assertFalse($client->getResponse()->isRedirect());
        }
    }

    public function testDeactivateAndRestore(): void
    {
        $client = static::createClient();
        $name = 'Lifecycle-' . bin2hex(random_bytes(4));
        $id = $this->createCategory($name);
        $crawler = $client->request('GET', '/categories', ['q' => $name]);
        $client->submit($crawler->filter('form[action="/categories/' . $id . '/deactivate"]')->form());
        self::assertResponseRedirects('/categories');
        self::assertFalse($this->reload($id)->isActive());
        self::assertNotNull($this->reload($id)->getUpdatedAt());

        $client->request('GET', '/categories', ['q' => $name]);
        self::assertSelectorNotExists('tbody tr');
        $crawler = $client->request('GET', '/categories', ['q' => $name, 'status' => 'inactive']);
        $client->submit($crawler->filter('form[action="/categories/' . $id . '/restore"]')->form());
        self::assertResponseRedirects('/categories?status=inactive');
        self::assertTrue($this->reload($id)->isActive());
    }

    public function testStatusChangesRequirePostAndValidCsrf(): void
    {
        $client = static::createClient();
        $id = $this->createCategory('Protected-' . bin2hex(random_bytes(4)));
        foreach (['deactivate', 'restore'] as $action) {
            $manager = static::getContainer()->get('doctrine')->getManager();
            $category = $manager->getRepository(AssetCategory::class)->find($id);
            $category->setIsActive($action === 'deactivate');
            $manager->flush();
            $client->request('GET', '/categories/' . $id . '/' . $action);
            self::assertResponseStatusCodeSame(405);
            $client->request('POST', '/categories/' . $id . '/' . $action, ['_token' => 'invalid']);
            // The firewall returns 401 for an anonymous AccessDeniedException.
            self::assertResponseStatusCodeSame(401);
            self::assertSame($action === 'deactivate', $this->reload($id)->isActive());
        }
    }

    public function testMissingCategoryReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/categories/2147483647/edit');
        self::assertResponseStatusCodeSame(404);
    }

    public function testInitialCategoriesCanBeLoadedTwiceWithoutDuplicates(): void
    {
        static::bootKernel();
        $manager = static::getContainer()->get('doctrine')->getManager();
        $fixtures = new AssetCategoryFixtures();
        $fixtures->load($manager);
        $repository = $manager->getRepository(AssetCategory::class);
        $counts = [];
        foreach (AssetCategoryFixtures::NAMES as $name) {
            $counts[$name] = $repository->count(['name' => $name]);
            self::assertGreaterThanOrEqual(1, $counts[$name]);
        }
        $fixtures->load($manager);
        foreach ($counts as $name => $count) {
            self::assertSame($count, $repository->count(['name' => $name]));
        }
    }

    private function createCategory(string $name, bool $active = true): int
    {
        $manager = static::getContainer()->get('doctrine')->getManager();
        $category = (new AssetCategory())->setName($name)->setIsActive($active);
        $manager->persist($category);
        $manager->flush();

        return $category->getId();
    }

    private function reload(int $id): AssetCategory
    {
        $manager = static::getContainer()->get('doctrine')->getManager();
        $manager->clear();

        return $manager->getRepository(AssetCategory::class)->find($id);
    }
}
