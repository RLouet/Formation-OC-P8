<?php

namespace App\Tests\App\Controller;

use App\Tests\App\TestCase;

/**
 * @internal
 * @coversDefaultClass \App\Controller\TaskController
 */
final class TaskControllerTest extends TestCase
{
    /**
     * @covers ::listAction
     */
    public function testListNotConnected()
    {
        $this->client->request('GET', '/tasks');

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('login');
    }

    /**
     * @covers ::listAction
     */
    public function testListValid()
    {
        $this->loginAs('user');
        $this->client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('task_list');
    }
}
