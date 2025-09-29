<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\BackupManager;
use App\Service\EmailHelper;
use DateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(name: 'app:backup:db', description: 'Backup the database')]
class BackupDbCommand
{
    public function __construct(
        private readonly BackupManager   $backupManager,
        private readonly EmailHelper     $emailHelper,
        private readonly MailerInterface $mailer,
    )
    {
    }

    public function __invoke(OutputInterface $output): int
    {
        $date = (new DateTime())->format('Y-m-d_H-i-s');
        $backupFile = sys_get_temp_dir() . sprintf('/backup_%s.sql', $date);

        $command = $this->backupManager->getBackupCommand() . ' > ' . escapeshellarg($backupFile);

        system($command, $result);

        if ($result !== 0) {
            $output->writeln('<error>Database backup failed</error>');
            return Command::FAILURE;
        }

        $email = $this->emailHelper
            ->createAdminEmail('Backup: ' . $date)
            ->text('Backup: ' . $date)
            ->attachFromPath($backupFile);

        $this->mailer->send($email);

        return Command::SUCCESS;
    }
}
