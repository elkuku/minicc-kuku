<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var UserRepository $repository */
        $repository = self::getContainer()->get(UserRepository::class);
        $this->repository = $repository;
    }

    public function testFindActiveUsersReturnsOnlyActiveRoleUsers(): void
    {
        $users = $this->repository->findActiveUsers();

        self::assertNotEmpty($users);

        foreach ($users as $user) {
            self::assertSame('ROLE_USER', $user->getRole());
            self::assertTrue($user->isIsActive());
        }
    }

    public function testFindActiveUsersAreSortedByName(): void
    {
        $users = $this->repository->findActiveUsers();

        $names = array_map(static fn(User $u): ?string => $u->getName(), $users);
        $sorted = $names;
        sort($sorted);

        self::assertSame($sorted, $names);
    }

    public function testGetSortedByStoreReturnsActiveUsers(): void
    {
        $users = $this->repository->getSortedByStore();

        self::assertNotEmpty($users);

        foreach ($users as $user) {
            self::assertSame('ROLE_USER', $user->getRole());
            self::assertTrue($user->isIsActive());
        }
    }
}
