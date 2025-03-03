<?php

namespace App\Controller;

use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\EmailHelper;
use App\Service\PayrollHelper;
use App\Service\PdfHelper;
use Exception;
use Knp\Snappy\Pdf;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UnexpectedValueException;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/mail')]
class MailController extends AbstractController
{





    #[Route(path: '/backup', name: 'backup', methods: ['GET'])]
    public function backup(
        MailerInterface $mailer,
        EmailHelper     $emailHelper,
        #[Autowire('%env(APP_ENV)%')]
        string          $appEnv,
    ): RedirectResponse
    {
        try {
            $parts = parse_url((string)$_ENV['DATABASE_URL']);

            $hostname = $parts['host'];
            $username = $parts['user'];
            $password = $parts['pass'];
            $database = ltrim($parts['path'], '/');

            $cmd = match ($appEnv) {
                'dev' => sprintf(
                    'docker exec minicc-kuku-database-1 /usr/bin/mysqldump -h %s -u %s -p%s %s|gzip 2>&1',
                    $hostname,
                    $username,
                    $password,
                    $database
                ),
                'prod' => sprintf(
                    'mysqldump -h %s -u %s -p%s %s|gzip 2>&1',
                    $hostname,
                    $username,
                    $password,
                    $database
                ),
                default => throw new UnexpectedValueException('Unknown env:' . $appEnv),
            };

            ob_start();
            passthru($cmd, $retVal);
            $gzip = ob_get_clean();

            if ($retVal) {
                throw new RuntimeException('Error creating DB backup: ' . $gzip);
            }

            $fileName = date('Y-m-d') . '_backup.gz';
            $mime = 'application/x-gzip';

            $attachment = new DataPart((string)$gzip, $fileName, $mime);

            $email = $emailHelper->createEmail(
                to: $emailHelper->getFrom(),
                subject: 'Backup'
            )
                ->text('Backup - Date: ' . date('Y-m-d'))
                ->html('<h3>Backup</h3>Date: ' . date('Y-m-d'))
                ->addPart($attachment);

            $mailer->send($email);
            $this->addFlash('success', 'Backup has been sent to your inbox.');
        } catch (TransportExceptionInterface $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin-tasks');
    }


}
