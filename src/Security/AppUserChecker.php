<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @see \App\Tests\Security\AppUserCheckerTest
 */
class AppUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        $this->checkIsActive($user);
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        $this->checkIsActive($user);
    }

    private function checkIsActive(UserInterface $user): void
    {
        if ($user instanceof User && false === $user->isIsActive()) {
            throw new DisabledException('Account is disabled.');
        }
    }
}