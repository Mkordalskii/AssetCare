<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Home | AssetCare');
        self::assertSelectorCount(1, 'main');
        self::assertSelectorTextContains('h1', 'Your assets, all in one place.');
        self::assertSelectorExists('nav a[href="/"][aria-current="page"]');
        self::assertSelectorExists('nav a[href="/assets"]');
        self::assertSelectorExists('nav a[href="/manufacturers"]');
        self::assertSelectorExists('nav a[href="/categories"]');
        self::assertSelectorExists('main a[href="/categories"]');
        self::assertSelectorExists('button[data-bs-target="#mainNavbar"]');
        self::assertSelectorExists('#mainNavbar.collapse.navbar-collapse');
        self::assertSelectorExists('main a[href="/assets/create"]');
        self::assertSelectorExists('main a[href="/assets"]');
        self::assertSelectorExists('main a[href="/manufacturers"]');
    }
}
