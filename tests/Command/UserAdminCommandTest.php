<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\UserAdminCommand;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Elkuku\SymfonyUtils\Command\UserAdminBaseCommand;
use PHPUnit\Framework\TestCase;

final class UserAdminCommandTest extends TestCase
{
    private UserAdminCommand $command;

    protected function setUp(): void
    {
        $this->command = new UserAdminCommand(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(UserRepository::class),
        );
    }

    public function testExtendsUserAdminBaseCommand(): void
    {
        self::assertInstanceOf(UserAdminBaseCommand::class, $this->command);
    }

    public function testCommandName(): void
    {
        self::assertSame('user-admin', $this->command->getName());
    }

    public function testCommandDescription(): void
    {
        self::assertSame('Administer user accounts', $this->command->getDescription());
    }

    public function testCommandAliases(): void
    {
        self::assertSame(['useradmin', 'admin'], $this->command->getAliases());
    }
}
