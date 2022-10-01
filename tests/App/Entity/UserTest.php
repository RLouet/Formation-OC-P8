<?php

namespace App\Tests\App\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Entity\User
 */
final class UserTest extends WebTestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testId(): void
    {
        $this->expectError();
        $this->user->getId();
    }

    public function tesDefaultRoles(): void
    {
        $this->assertSame(['ROLE_USER'], $this->user->getRoles());
    }

    public function testUsername(): void
    {
        $this->user->setUsername('JohnDoe');
        $this->assertSame('JohnDoe', $this->user->getUsername());
        $this->assertSame('JohnDoe', $this->user->getUserIdentifier());
    }

    public function testEmail(): void
    {
        $this->user->setEmail('email@test.com');
        $this->assertSame('email@test.com', $this->user->getEmail());
    }

    public function testRoles(): void
    {
        $this->user->setRoles(['ROLE_ADMIN']);
        $this->assertSame(['ROLE_ADMIN', 'ROLE_USER'], $this->user->getRoles());
    }

    public function testPassword(): void
    {
        $this->user->setPassword('MyStrongPassword123');
        $this->assertSame('MyStrongPassword123', $this->user->getPassword());
    }
}