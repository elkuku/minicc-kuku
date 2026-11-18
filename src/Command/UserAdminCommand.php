<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use App\Type\Gender;
use BackedEnum;
use Doctrine\ORM\EntityManagerInterface;
use Elkuku\SymfonyUtils\Command\UserAdminBaseCommand;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @see \App\Tests\Command\UserAdminCommandTest
 */
#[AsCommand(
    name: 'user-admin',
    description: 'Administer user accounts',
    aliases: ['useradmin', 'admin']
)]
class UserAdminCommand extends UserAdminBaseCommand
{
    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
    ) {
        parent::__construct($entityManager, $userRepository);
    }

    /**
     * @return array<BackedEnum>
     */
    protected function getRoles(): array
    {
        return UserRole::cases();
    }

    /**
     * @param User $user
     */
    protected function setRole(UserInterface $user, string $role): User
    {
        return $user->setRole(UserRole::from($role));
    }

    /**
     * @param User $user
     */
    protected function fillNewUser(UserInterface $user, SymfonyStyle $io): User
    {
        $name = $io->ask('Name', null, static function (mixed $name): string {
            $name = trim(is_scalar($name) ? (string) $name : '');

            if ('' === $name || mb_strlen($name) > 40) {
                throw new RuntimeException('The name is required and can have at most 40 characters.');
            }

            return $name;
        });

        $gender = $io->choice('Gender', array_keys(Gender::getChoices()), Gender::other->name);

        return $user
            ->setName(is_string($name) ? $name : '')
            ->setGender(Gender::from(Gender::getChoices()[is_string($gender) ? $gender : Gender::other->name]));
    }
}
