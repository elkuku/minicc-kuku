<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\BackupDbCommand;
use App\Service\BackupManager;
use App\Service\EmailHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class BackupDbCommandTest extends TestCase
{
    public function testFailureWhenBackupCommandFails(): void
    {
        $backupManager = $this->createStub(BackupManager::class);
        $backupManager->method('getBackupCommand')
            ->willReturn('false');

        $emailHelper = $this->createStub(EmailHelper::class);

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->never())->method('send');

        $command = new BackupDbCommand($backupManager, $emailHelper, $mailer);
        $output = new BufferedOutput();

        $result = $command($output);

        $this->assertSame(Command::FAILURE, $result);
        $this->assertStringContainsString('Database backup failed', $output->fetch());
    }

    public function testSuccessWhenBackupCommandSucceeds(): void
    {
        $backupManager = $this->createStub(BackupManager::class);
        $backupManager->method('getBackupCommand')
            ->willReturn('echo test');

        $email = new Email();
        $email->from('test@example.com')->to('test@example.com');

        $emailHelper = $this->createStub(EmailHelper::class);
        $emailHelper->method('createAdminEmail')
            ->willReturn($email);

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('send');

        $command = new BackupDbCommand($backupManager, $emailHelper, $mailer);
        $output = new BufferedOutput();

        $result = $command($output);

        $this->assertSame(Command::SUCCESS, $result);
    }
}
