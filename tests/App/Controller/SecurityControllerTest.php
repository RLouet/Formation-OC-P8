<?php

namespace App\Tests\App\Controller;

use App\Tests\App\TestCase;

/**
 * @internal
 * @coversDefaultClass \App\Controller\SecurityController
 */
final class SecurityControllerTest extends TestCase
{
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
        $this->loginAs('user');
        $this->client->request('GET', '/logout');

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('homepage');
        $this->assertSelectorExists('a:contains("Se connecter")');
        $this->assertSelectorNotExists('a:contains("Se déconnecter")');
    }
}
