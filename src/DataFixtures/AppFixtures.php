<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin
            ->setEmail('admin@todolist.test')
            ->setUsername('admin')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->hasher->hashPassword($admin, 'password'))
        ;

        $user = new User();
        $user
            ->setEmail('user@todolist.test')
            ->setUsername('user')
            ->setRoles(['ROLE_USER'])
            ->setPassword($this->hasher->hashPassword($user, 'password'))
        ;

        $manager->persist($admin);
        $manager->persist($user);

        $todoTask = new Task();
        $todoTask
            ->setAuthor($user)
            ->setContent('This user task is not done')
            ->setTitle('TODO Task')
        ;

        $doneTask = new Task();
        $doneTask
            ->setAuthor($admin)
            ->setContent('This admin task is done.')
            ->setTitle('DONE Task')
            ->toggle(true)
        ;

        $anonymousTask = new Task();
        $anonymousTask
            ->setAuthor($admin)
            ->setContent('This anonymous task is done.')
            ->setTitle('Anonymous DONE Task')
            ->toggle(true)
        ;

        $manager->persist($todoTask);
        $manager->persist($doneTask);
        $manager->persist($anonymousTask);

        $manager->flush();
    }
}
