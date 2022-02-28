<?php

namespace App\Security\Voter;

use App\Entity\Task;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    private const DELETE = 'delete';

    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::DELETE === $attribute
            && $subject instanceof Task;
    }

    protected function voteOnAttribute(string $attribute, $task, TokenInterface $token): bool
    {
        /** @var Task $task */
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return $task->getAuthor() ? $user === $task->getAuthor() : $this->security->isGranted('ROLE_ADMIN');
    }
}
