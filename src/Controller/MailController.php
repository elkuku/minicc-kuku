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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UnexpectedValueException;

#[IsGranted('ROLE_ADMIN')]
class MailController extends AbstractController
{
    #[Route(path: '/mail-transactions', name: 'mail-transactions', methods: ['POST'])]
    public function mail(
        StoreRepository $storeRepository,
        TransactionRepository $transactionRepository,
        Request $request,
        Pdf $pdf,
        PdfHelper $PDFHelper,
        MailerInterface $mailer,
        EmailHelper $emailHelper,
    ): RedirectResponse {
        $recipients = $request->get('recipients');
        $year = (int)date('Y');
        if (!$recipients) {
            $this->addFlash('warning', 'No recipients selected');

            return $this->redirectToRoute('mail-list-transactions');
        }
        $stores = $storeRepository->getActive();
        $failures = [];
        $successes = [];
        foreach ($stores as $store) {
            if (!array_key_exists((int)$store->getId(), $recipients)) {
                continue;
            }

            $fileName = "movimientos-{$store->getId()}-$year.pdf";
            $html = $this->renderView(
                '_mail/client-transactions.twig',
                [
                    'user'     => $store->getUser(),
                    'store'    => $store,
                    'fileName' => $fileName,
                    'year'     => $year,
                ]
            );

            $document = $pdf->getOutputFromHtml(
                $PDFHelper->renderTransactionHtml(
                    $transactionRepository,
                    $store,
                    $year
                )
            );

            $email = $emailHelper
                ->create(
                    toAddress: (string)$store->getUser()?->getEmail(),
                    subject: "Movimientos del local {$store->getId()} ano $year"
                )
                ->html($html)
                ->attach($document, $fileName);

            try {
                $mailer->send($email);
                $successes[] = $store->getId();
            } catch (TransportExceptionInterface $exception) {
                $failures[] = $exception->getMessage();
            }
        }
        if ($failures) {
            $this->addFlash('warning', implode('<br>', $failures));
        }
        if ($successes) {
            $this->addFlash(
                'success',
                'Mails have been sent to stores: '
                .implode(', ', $successes)
            );
        }

        return $this->redirectToRoute('welcome');
    }

    #[Route(path: '/mail-annual-transactions', name: 'mail-annual-transactions', methods: ['POST'])]
    public function mailStores(
        Request $request,
        TransactionRepository $transactionRepository,
        StoreRepository $storeRepository,
        Pdf $pdf,
        PdfHelper $PDFHelper,
        MailerInterface $mailer,
        EmailHelper $emailHelper,
    ): RedirectResponse {
        $year = (int)$request->get('year', date('Y'));
        $htmlPages = [];
        $stores = $storeRepository->findAll();
        foreach ($stores as $store) {
            if ($store->getUserId()) {
                $htmlPages[] = $PDFHelper->renderTransactionHtml(
                    $transactionRepository,
                    $store,
                    $year
                );
            }
        }
        $fileName = "movimientos-$year.pdf";
        try {
            $attachment = new DataPart(
                $pdf->getOutputFromHtml($htmlPages),
                $fileName,
                'application/pdf'
            );

            $email = $emailHelper->create(
                toAddress: 'minicckuku@gmail.com',
                subject: "Movimientos de los locales ano $year"
            )
                ->text('Backup - Date: '.date('Y-m-d'))
                ->html('<h3>Backup</h3>Date: '.date('Y-m-d'))
                ->addPart($attachment);

            $mailer->send($email);
            $this->addFlash('success', 'Mail has been sent succesfully.');
        } catch (Exception|TransportExceptionInterface $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('welcome');
    }

    #[Route(path: '/planillas-mail', name: 'planillas-mail', methods: ['GET'])]
    public function mailPlanillas(
        Pdf $pdf,
        PdfHelper $PDFHelper,
        MailerInterface $mailer,
        EmailHelper $emailHelper,
        PayrollHelper $payrollHelper,
    ): Response {
        $year = (int)date('Y');
        $month = (int)date('m');
        $fileName = "payrolls-$year-$month.pdf";
        $html = 'Attachment: '.$fileName;
        $document = $pdf->getOutputFromHtml(
            $PDFHelper->renderPayrollsHtml(
                $year,
                $month,
                $payrollHelper
            ),
            ['enable-local-file-access' => true]
        );
        $email = $emailHelper->create(
            toAddress: 'minicckuku@gmail.com',
            subject: "Planillas $year-$month"
        )
            ->subject("Planillas $year-$month")
            ->html($html)
            ->attach($document, $fileName);
        try {
            $mailer->send($email);
            $this->addFlash('success', 'Mail has been sent.');
        } catch (TransportExceptionInterface $e) {
            $this->addFlash('danger', 'ERROR sending mail: '.$e->getMessage());
        }

        return $this->render('admin/tasks.html.twig');
    }

    #[Route(path: '/planilla-mail', name: 'planilla-mail', methods: ['POST'])]
    public function mailPlanillasClients(
        StoreRepository $storeRepository,
        Request $request,
        Pdf $pdf,
        PdfHelper $PDFHelper,
        MailerInterface $mailer,
        EmailHelper $emailHelper,
        PayrollHelper $payrollHelper
    ): RedirectResponse {
        $recipients = $request->get('recipients');
        if (!$recipients) {
            $this->addFlash('warning', 'No recipients selected');

            return $this->redirectToRoute('mail-list-transactions');
        }
        $year = (int)date('Y');
        $month = (int)date('m');
        $fileName = "planilla-$year-$month.pdf";
        $stores = $storeRepository->getActive();
        $failures = [];
        $successes = [];

        foreach ($stores as $store) {
            if (!array_key_exists((int)$store->getId(), $recipients)) {
                continue;
            }

            $document = $pdf->getOutputFromHtml(
                $PDFHelper->renderPayrollsHtml(
                    $year,
                    $month,
                    $payrollHelper,
                    (int)$store->getId()
                ),
                ['enable-local-file-access' => true]
            );

            $html = $this->renderView(
                '_mail/client-planillas.twig',
                [
                    'user'     => $store->getUser(),
                    'store'    => $store,
                    'factDate' => "$year-$month-1",
                    'fileName' => $fileName,
                ]
            );

            $user = $store->getUser();

            if (!$user) {
                continue;
            }

            $email = $emailHelper->create(
                toAddress: $user->getEmail(),
                subject: "Su planilla del local {$store->getId()} ($month - $year)"
            )
                ->html($html)
                ->attach($document, $fileName);

            try {
                $mailer->send($email);
                $successes[] = $store->getId();
            } catch (TransportExceptionInterface $exception) {
                $failures[] = $exception->getMessage();
            }
        }
        if ($failures) {
            $this->addFlash('warning', implode('<br>', $failures));
        }
        if ($successes) {
            $this->addFlash(
                'success',
                'Mails have been sent to stores: '
                .implode(', ', $successes)
            );
        }

        return $this->redirectToRoute('welcome');
    }

    #[Route(path: '/backup', name: 'backup', methods: ['GET'])]
    public function backup(
        MailerInterface $mailer,
        EmailHelper $emailHelper,
        string $appEnv,
    ): RedirectResponse {
        try {
            $parts = parse_url($_ENV['DATABASE_URL']);

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
                default => throw new UnexpectedValueException('Unknown env:'.$appEnv),
            };

            ob_start();
            passthru($cmd, $retVal);
            $gzip = ob_get_clean();

            if ($retVal) {
                throw new RuntimeException('Error creating DB backup: '.$gzip);
            }

            $fileName = date('Y-m-d').'_backup.gz';
            $mime = 'application/x-gzip';

            $attachment = new DataPart((string)$gzip, $fileName, $mime);

            $email = $emailHelper->create(
                toAddress: 'minicckuku@gmail.com',
                subject: 'Backup'
            )
                ->text('Backup - Date: '.date('Y-m-d'))
                ->html('<h3>Backup</h3>Date: '.date('Y-m-d'))
                ->addPart($attachment);

            $mailer->send($email);
            $this->addFlash('success', 'Backup has been sent to your inbox.');
        } catch (TransportExceptionInterface $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin-tasks');
    }
}
