<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\RegenerateAppSecretCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

final class RegenerateAppSecretCommandTest extends TestCase
{
    public function testInvokeOutputsSecretKey(): void
    {
        $command = new RegenerateAppSecretCommand();

        $io = $this->createMock(SymfonyStyle::class);
        $io->expects(self::once())
            ->method('success')
            ->with(self::callback(function (string $msg): bool {
                self::assertStringContainsString('Your secret key', $msg);
                self::assertStringContainsString('APP_SECRET', $msg);
                // Secret should be 32 hex characters
                preg_match('/Your secret key (\w+)/', $msg, $matches);
                self::assertSame(32, strlen($matches[1]));

                return true;
            }));

        $result = $command($io);

        self::assertSame(Command::SUCCESS, $result);
    }
}
