<?php

namespace App\Tests\App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Controller\TaskController
 */
final class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->userRepository = self::getContainer()->get(ManagerRegistry::class)->getRepository(User::class);
    }

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
        $this->client->loginUser($this->userRepository->findOneBy(['email' => 'user@todolist.test']));
        $this->client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('task_list');
    }
}
