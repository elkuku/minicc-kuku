<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\UserAdminCommand;
use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use App\Type\Gender;
use Doctrine\ORM\EntityManagerInterface;
use Elkuku\SymfonyUtils\Command\UserAdminBaseCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

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
        $this->assertInstanceOf(UserAdminBaseCommand::class, $this->command);
    }

    public function testCommandName(): void
    {
        $this->assertSame('user-admin', $this->command->getName());
    }

    public function testCommandDescription(): void
    {
        $this->assertSame('Administer user accounts', $this->command->getDescription());
    }

    public function testCommandAliases(): void
    {
        $this->assertSame(['useradmin', 'admin'], $this->command->getAliases());
    }

    public function testCreateUserAsksForNameAndGender(): void
    {
        $persisted = null;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist')
            ->willReturnCallback(static function (object $user) use (&$persisted): void {
                $persisted = $user;
            });
        $entityManager->expects($this->once())->method('flush');

        $tester = new CommandTester(new UserAdminCommand($entityManager, $this->createStub(UserRepository::class)));
        $tester->setInputs(['Create User', 'new@example.com', 'ROLE_CASHIER', 'New User', 'female', 'Exit']);

        $this->assertSame(Command::SUCCESS, $tester->execute([]));
        $this->assertStringContainsString('User created', $tester->getDisplay());

        $this->assertInstanceOf(User::class, $persisted);
        $this->assertSame('new@example.com', $persisted->getEmail());
        $this->assertSame('New User', $persisted->getName());
        $this->assertSame(UserRole::CASHIER, $persisted->getRole());
        $this->assertSame(Gender::female, $persisted->getGender());
    }

    public function testCreateUserRequiresAName(): void
    {
        $tester = new CommandTester(new UserAdminCommand(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(UserRepository::class),
        ));
        $tester->setInputs(['Create User', 'new@example.com', 'ROLE_USER', '', 'New User', 'other', 'Exit']);
        $tester->execute([]);

        $this->assertStringContainsString('The name is required', $tester->getDisplay());
        $this->assertStringContainsString('User created', $tester->getDisplay());
    }
}
