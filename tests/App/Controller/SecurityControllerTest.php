<?php

namespace App\Tests\App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Controller\SecurityController
 */
final class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    /**
     * @covers ::loginAction
     */
    public function testLoginValid()
    {
        $this->client->request('GET', '/login');

        $this->client->submitForm('Se connecter', [
            '_username' => 'admin',
            '_password' => 'password',
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('homepage');
        $this->assertSelectorExists('a:contains("Se déconnecter")');
    }

    /**
     * @covers ::loginAction
     */
    public function testLoginInvalid()
    {
        $this->client->request('GET', '/login');

        $this->client->submitForm('Se connecter', [
            '_username' => 'admin',
            '_password' => 'bad',
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('login');
        $this->assertSelectorExists('div:contains("Invalid credentials.")');
    }

    /**
     * @covers ::logout
     */
    public function testLogout()
    {
        $userRepository = self::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneBy(['email' => 'user@todolist.test']);
        $this->client->loginUser($testUser);

        $this->client->request('GET', '/logout');

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('homepage');
        $this->assertSelectorExists('a:contains("Se connecter")');
        $this->assertSelectorNotExists('a:contains("Se déconnecter")');
    }
}
