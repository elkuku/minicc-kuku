<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\BackupManager;
use App\Service\EmailHelper;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BackupDb extends AbstractController
{
    public function __construct(private readonly BackupManager $backupManager, private readonly EmailHelper $emailHelper, private readonly MailerInterface $mailer)
    {
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/admin/backup-db', name: 'admin_backup_db', methods: ['GET'])]
    public function index(): Response
    {
        $date = new DateTime()->format('Y-m-d_H-i-s');
        $backupFile = sys_get_temp_dir() . sprintf('/backup_%s.sql', $date);
        $command = $this->backupManager->getBackupCommand() . ' > ' . escapeshellarg($backupFile) . ' 2>&1';
        system($command, $result);
        if ($result !== 0) {
            $this->addFlash('error', 'Database backup failed');
        } else {
            $email = $this->emailHelper
                ->createAdminEmail('Backup: ' . $date)
                ->text('Backup: ' . $date)
                ->attachFromPath($backupFile);

            $this->mailer->send($email);

            $this->addFlash('success', 'Database backup has been sent to your email.');
        }

        return $this->render('admin/tasks.html.twig');
    }
}
