<?php

namespace App\Tests\App\Entity;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @coversDefaultClass \App\Entity\Task
 */
final class TaskTest extends WebTestCase
{
    private Task $task;

    protected function setUp(): void
    {
        $this->task = new Task();
    }

    public function testId(): void
    {
        $this->expectError();
        $this->task->getId();
    }

    public function testCreatedAt(): void
    {
        $this->assertTrue(
            ($this->task->getCreatedAt() > new \DateTimeImmutable('-1 minute'))
            && ($this->task->getCreatedAt() < new \DateTimeImmutable())
        );
    }

    public function testIsDone(): void
    {
        $this->assertFalse($this->task->isDone());
    }

    public function testTitle(): void
    {
        $this->task->setTitle('Mon super titre');
        $this->assertSame('Mon super titre', $this->task->getTitle());
    }

    public function testContent(): void
    {
        $this->task->setContent('Mon super contenu');
        $this->assertSame('Mon super contenu', $this->task->getContent());
    }

    public function testToggleDone(): void
    {
        $this->task->toggle(true);
        $this->assertTrue($this->task->isDone());
    }

    public function testToggleNotDone(): void
    {
        $this->task->toggle(false);
        $this->assertFalse($this->task->isDone());
    }

    public function testAuthor(): void
    {
        $author = (new User())
            ->setEmail('my-mail@exemple.com')
            ->setPassword('FakePassword123')
            ->setUsername('MyUsername')
            ->setRoles(['ROLE_USER'])
        ;

        $this->task->setAuthor($author);
        $this->assertSame($author, $this->task->getAuthor());
    }
}