<?php

namespace App\Tests\App\Controller;

use App\Tests\App\TestCase;

/**
 * @internal
 * @coversDefaultClass \App\Controller\UserController
 */
final class UserControllerTest extends TestCase
{
    /**
     * @covers ::listAction
     */
    public function testListNotConnected()
    {
        $this->client->request('GET', '/users');

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('login');
    }

    /**
     * @covers ::listAction
     */
    public function testListForbidden()
    {
        $this->loginAs('user');

        $this->client->request('GET', '/users');

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * @covers ::listAction
     */
    public function testListValid()
    {
        $this->loginAs('admin');
        $this->client->request('GET', '/users');

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('user_list');
    }
}
