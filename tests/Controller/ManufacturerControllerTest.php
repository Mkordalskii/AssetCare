<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\ManufacturerRepository;
use App\Entity\Manufacturer;
use Symfony\Component\Routing\RouterInterface;

final class ManufacturerControllerTest extends WebTestCase
{
    public function testManufacturerIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/manufacturers');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Manufacturers');
    }

    public function testCreatePageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/manufacturers/create');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Add manufacturer');
        self::assertSelectorExists('form');
    }

    public function testCreateManufacturer(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/manufacturers/create');
        self::assertResponseIsSuccessful();
        $form = $crawler->selectButton('Save')->form();
        $form['manufacturer[name]'] = 'Test Manufacturer';
        $form['manufacturer[website]'] = 'https://www.testmanufacturer.com';
        $form['manufacturer[supportEmail]'] = 'support@test-manufacturer.com';
        $form['manufacturer[supportPhone]'] = '+49123456789';

        $client->submit($form);
        self::assertResponseRedirects('/manufacturers');
        /** @var ManufacturerRepository $repository */

        $repository = static::getContainer()->get(ManufacturerRepository::class);

        $manufacturer = $repository->findOneBy([
            'name' => 'Test Manufacturer',
        ]);

        self::assertNotNull($manufacturer);
        self::assertSame(
            'https://www.testmanufacturer.com',
            $manufacturer->getWebsite()
        );
        self::assertSame(
            'support@test-manufacturer.com',
            $manufacturer->getSupportEmail()
        );
        self::assertSame(
            '+49123456789',
            $manufacturer->getSupportPhone()
        );
        self::assertTrue($manufacturer->isActive());

        $client->followRedirect();
        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains(
            '.alert-success',
            'Manufacturer was created successfully.'
        );

        self::assertSelectorTextContains(
            'body',
            'Test Manufacturer'
        );
    }

    public function testEditManufacturer(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $manufacturer = new Manufacturer();
        $manufacturer
            ->setName('Manufacturer Before Edit')
            ->setWebsite('https://before-edit.example.com')
            ->setSupportEmail('before@example.com')
            ->setSupportPhone('+49 111 111111');

        $entityManager->persist($manufacturer);
        $entityManager->flush();

        $id = $manufacturer->getId();
        $createdAt = $manufacturer->getCreatedAt();

        $crawler = $client->request(
            'GET',
            '/manufacturers/' . $id . '/edit'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();

        $form['manufacturer[name]'] = 'Manufacturer After Edit';
        $form['manufacturer[website]'] = 'https://after-edit.example.com';
        $form['manufacturer[supportEmail]'] = 'after@example.com';
        $form['manufacturer[supportPhone]'] = '+49 222 222222';

        $client->submit($form);

        self::assertResponseRedirects('/manufacturers');

        /** @var ManufacturerRepository $repository */
        $repository = static::getContainer()->get(
            ManufacturerRepository::class
        );

        $editedManufacturer = $repository->find($id);

        self::assertNotNull($editedManufacturer);

        self::assertSame(
            'Manufacturer After Edit',
            $editedManufacturer->getName()
        );

        self::assertSame(
            'https://after-edit.example.com',
            $editedManufacturer->getWebsite()
        );

        self::assertSame(
            'after@example.com',
            $editedManufacturer->getSupportEmail()
        );

        self::assertSame(
            '+49 222 222222',
            $editedManufacturer->getSupportPhone()
        );

        self::assertSame(
            $createdAt->format('Y-m-d H:i:s'),
            $editedManufacturer->getCreatedAt()->format('Y-m-d H:i:s')
        );

        self::assertNotNull(
            $editedManufacturer->getUpdatedAt()
        );
    }

    public function testDeleteAndRestoreManufacturer(): void
    {
        $client = static::createClient();

        /*
     * ARRANGE
     * Przygotowujemy producenta w testowej bazie danych.
     */
        $entityManager = static::getContainer()
            ->get('doctrine')
            ->getManager();

        $manufacturer = new Manufacturer();

        $manufacturer
            ->setName('Manufacturer To Delete')
            ->setWebsite('https://delete-test.example.com')
            ->setSupportEmail('delete@example.com')
            ->setSupportPhone('+49 333 333333');

        $entityManager->persist($manufacturer);
        $entityManager->flush();

        $id = $manufacturer->getId();

        self::assertNotNull($id);
        self::assertTrue($manufacturer->isActive());

        /*
     * DELETE
     *
     * Otwieramy prawdziwą listę producentów.
     * Twig wygeneruje formularz Delete razem z prawidłowym CSRF tokenem.
     */
        $crawler = $client->request(
            'GET',
            '/manufacturers'
        );

        self::assertResponseIsSuccessful();

        $deleteForm = $crawler
            ->filter(
                'form[action="/manufacturers/' . $id . '/delete"]'
            )
            ->form();

        /*
     * Wysyłamy dokładnie formularz wygenerowany przez aplikację.
     * Razem z ukrytym polem _token.
     */
        $client->submit($deleteForm);

        self::assertResponseRedirects('/manufacturers');

        /*
     * WAŻNE:
     * submit() wykonał kolejny request, więc Symfony mogło
     * zrestartować kernel i kontener.
     *
     * Pobieramy więc NOWY EntityManager i Repository.
     */
        $entityManager = static::getContainer()
            ->get('doctrine')
            ->getManager();

        $entityManager->clear();

        /** @var ManufacturerRepository $repository */
        $repository = static::getContainer()->get(
            ManufacturerRepository::class
        );

        $deletedManufacturer = $repository->find($id);

        self::assertNotNull($deletedManufacturer);
        self::assertFalse($deletedManufacturer->isActive());
        self::assertNotNull($deletedManufacturer->getUpdatedAt());

        /*
     * RESTORE
     *
     * Producent jest teraz nieaktywny, więc otwieramy listę inactive.
     */
        $crawler = $client->request(
            'GET',
            '/manufacturers?status=inactive'
        );

        self::assertResponseIsSuccessful();

        /*
     * Znajdujemy prawdziwy formularz Restore.
     * Zawiera już poprawny CSRF token.
     */
        $restoreForm = $crawler
            ->filter(
                'form[action="/manufacturers/' . $id . '/restore"]'
            )
            ->form();

        $client->submit($restoreForm);

        /*
     * Twój kontroler po restore przekierowuje z powrotem
     * do listy nieaktywnych.
     */
        self::assertResponseRedirects(
            '/manufacturers?status=inactive'
        );

        /*
     * Ponownie nastąpił request.
     * Ponownie pobieramy świeży EntityManager i Repository.
     */
        $entityManager = static::getContainer()
            ->get('doctrine')
            ->getManager();

        $entityManager->clear();

        /** @var ManufacturerRepository $repository */
        $repository = static::getContainer()->get(
            ManufacturerRepository::class
        );

        $restoredManufacturer = $repository->find($id);

        self::assertNotNull($restoredManufacturer);
        self::assertTrue($restoredManufacturer->isActive());
        self::assertNotNull($restoredManufacturer->getUpdatedAt());
    }
}
