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
    #[Route(path: '/transactions-clients', name: 'mail_transactions_clients', methods: ['GET', 'POST'])]
    public function transactionsClients(
        StoreRepository       $storeRepository,
        TransactionRepository $transactionRepository,
        Request               $request,
        Pdf                   $pdf,
        PdfHelper             $PDFHelper,
        MailerInterface       $mailer,
        EmailHelper           $emailHelper,
    ): Response
    {
        $recipients = $request->get('recipients');

        if (!$recipients) {
            return $this->render(
                'admin/mail-list-transactions.twig',
                [
                    'stores' => $storeRepository->getActive(),
                    'years' => range(date('Y'), date('Y', strtotime('-5 year'))),
                ]
            );
        }

        $year = (int)$request->get('year', date('Y'));
        $stores = $storeRepository->getActive();
        $failures = [];
        $successes = [];

        foreach ($stores as $store) {
            if (!array_key_exists((int)$store->getId(), $recipients)) {
                continue;
            }

            $fileName = "movimientos-{$store->getId()}-$year.pdf";

            $document = $pdf->getOutputFromHtml(
                $PDFHelper->renderTransactionHtml(
                    $transactionRepository,
                    $store,
                    $year
                )
            );

            $email = $emailHelper
                ->createTemplatedEmail(
                    to: new Address((string)$store->getUser()?->getEmail(), (string)$store->getUser()?->getName()),
                    subject: "Movimientos del local {$store->getId()} ano $year"
                )
                ->htmlTemplate('email/client-transactions.twig')
                ->context([
                    'user' => $store->getUser(),
                    'store' => $store,
                    'fileName' => $fileName,
                    'year' => $year,
                ])
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
                . implode(', ', $successes)
            );
        }

        return $this->redirectToRoute('welcome');
    }

    #[Route(path: '/mail-annual-transactions', name: 'mail-annual-transactions', methods: ['POST'])]
    public function transactions(
        Request               $request,
        TransactionRepository $transactionRepository,
        StoreRepository       $storeRepository,
        Pdf                   $pdf,
        PdfHelper             $PDFHelper,
        MailerInterface       $mailer,
        EmailHelper           $emailHelper,
    ): RedirectResponse
    {
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

            $email = $emailHelper->createEmail(
                to: $emailHelper->getFrom(),
                subject: "Movimientos de los locales ano $year"
            )
                ->text('Backup - Date: ' . date('Y-m-d'))
                ->html('<h3>Backup</h3>Date: ' . date('Y-m-d'))
                ->addPart($attachment);

            $mailer->send($email);
            $this->addFlash('success', 'Mail has been sent successfully.');
        } catch (Exception|TransportExceptionInterface $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('welcome');
    }

    #[Route(path: '/planillas-mail', name: 'planillas-mail', methods: ['GET'])]
    public function planillas(
        Pdf             $pdf,
        PdfHelper       $PDFHelper,
        MailerInterface $mailer,
        EmailHelper     $emailHelper,
        PayrollHelper   $payrollHelper,
    ): Response
    {
        $year = (int)date('Y');
        $month = (int)date('m');
        $fileName = "payrolls-$year-$month.pdf";
        $html = 'Attachment: ' . $fileName;
        $document = $pdf->getOutputFromHtml(
            $PDFHelper->renderPayrollsHtml(
                $year,
                $month,
                $payrollHelper
            ),
            [
                'enable-local-file-access' => true,
            ]
        );
        $email = $emailHelper->createEmail(
            to: $emailHelper->getFrom(),
            subject: "Planillas $year-$month"
        )
            ->subject("Planillas $year-$month")
            ->html($html)
            ->attach($document, $fileName);
        try {
            $mailer->send($email);
            $this->addFlash('success', 'Mail has been sent.');
        } catch (TransportExceptionInterface $e) {
            $this->addFlash('danger', 'ERROR sending mail: ' . $e->getMessage());
        }

        return $this->render('admin/tasks.html.twig');
    }

    #[Route(path: '/planillas-clients', name: 'mail_planillas_clients', methods: ['GET', 'POST'])]
    public function planillasClients(
        StoreRepository $storeRepository,
        Request         $request,
        Pdf             $pdf,
        PdfHelper       $PDFHelper,
        MailerInterface $mailer,
        EmailHelper     $emailHelper,
        PayrollHelper   $payrollHelper
    ): Response
    {
        $recipients = $request->get('recipients');
        if ($recipients) {
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

                $user = $store->getUser();

                if (!$user) {
                    continue;
                }

                $document = $pdf->getOutputFromHtml(
                    $PDFHelper->renderPayrollsHtml(
                        $year,
                        $month,
                        $payrollHelper,
                        (int)$store->getId()
                    ),
                    [
                        'enable-local-file-access' => true,
                    ]
                );

                $email = $emailHelper->createTemplatedEmail(
                    to: new Address($user->getEmail(), $user->getName()),
                    subject: "Su planilla del local {$store->getId()} ($month - $year)"
                )
                    ->htmlTemplate('email/client-planillas.twig')
                    ->context([
                        'user' => $store->getUser(),
                        'store' => $store,
                        'factDate' => "$year-$month-1",
                        'fileName' => $fileName,
                        'payroll' => $payrollHelper->getData($year, $month, $store->getId()),
                    ])
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
                    . implode(', ', $successes)
                );
            }

            return $this->redirectToRoute('welcome');
        }
        return $this->render(
            'admin/mail-list-planillas.twig',
            [
                'stores' => $storeRepository->getActive(),
            ]
        );

    }

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

    #[Route(path: '/cobros-contador', name: 'mail_cobros_contador', methods: ['GET', 'POST'])]
    public function paymentsAccountant(
        Request                                       $request,
        TransactionRepository                         $repository,
        EmailHelper                                   $emailHelper,
        MailerInterface                               $mailer,
        #[Autowire('%env(EMAIL_ACCOUNTANT)%')] string $emailAccountant,
    ): Response
    {
        $year = $request->request->getInt('year', (int)date('Y'));
        $month = $request->request->getInt('month', (int)date('m'));
        $ii = $request->get('ids');
        $ids = is_array($ii) ? array_filter($ii, 'is_numeric') : [];

        if ($ids) {
            $email = $emailHelper->createTemplatedEmail(
                to: Address::create($emailAccountant),
                subject: "Pagos del MiniCC KuKu - $month / $year"
            )
                ->htmlTemplate('email/cobros-contador.twig')
                ->context([
                    'year' => $year,
                    'month' => $month,
                    'payments' => $repository->findByIds($ids),
                    'fileName' => '@todo$fileName',//@todo ->attach($document, $fileName)
                ]);

            try {
                $mailer->send($email);
                $this->addFlash('success', 'Payments have been mailed.');
            } catch (TransportExceptionInterface $exception) {
                $this->addFlash('danger', 'Payments have NOT been mailed! - ' . $exception->getMessage());
            }

            //@todo redirect elsewhere
            //return $this->redirectToRoute('mail_cobros_contador');
        }

        return $this->render('mail/cobros-contador.html.twig',
            [
                'month' => $month,
                'year' => $year,
                'payments' => $repository->findByDate($year, $month),
            ]
        );
    }
}
