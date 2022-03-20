<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @coversNothing
 */
final class DefaultControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testHomeIsUp()
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        echo $this->client->getResponse()->setCharset('utf-8')->getContent();
    }
}
