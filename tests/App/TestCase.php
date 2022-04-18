<?php

namespace App\Tests\App;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class TestCase extends WebTestCase
{
    protected KernelBrowser $client;
    private User $user;
    private User $admin;

    protected function setUp(): void
    {
        $userRepository = self::getContainer()->get(ManagerRegistry::class)->getRepository(User::class);

        $this->client = self::createClient();
        $this->user = $userRepository->findOneBy(['email' => 'user@todolist.test']);
        $this->admin = $userRepository->findOneBy(['email' => 'admin@todolist.test']);
    }

    protected function loginAs(string $role): void
    {
        switch ($role) {
            case 'user':
                $this->client->loginUser($this->user);

                break;

            case 'admin':
                $this->client->loginUser($this->admin);

                break;
        }
    }
}
