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
        $io->expects($this->once())
            ->method('success')
            ->with(self::callback(function (string $msg): bool {
                $this->assertStringContainsString('Your secret key', $msg);
                $this->assertStringContainsString('APP_SECRET', $msg);
                $this->assertMatchesRegularExpression('/Your secret key \w{32}\b/', $msg);

                return true;
            }));

        $result = $command($io);

        $this->assertSame(Command::SUCCESS, $result);
    }
}
