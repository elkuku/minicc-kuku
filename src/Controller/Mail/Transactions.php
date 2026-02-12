<?php

declare(strict_types=1);

namespace App\Controller\Mail;

use Exception;
use App\Controller\BaseController;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\EmailHelper;
use App\Service\PdfHelper;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/mail/transactions', name: 'mail_transactions', methods: ['POST'])]
class Transactions extends BaseController
{
    public function __construct(private readonly TransactionRepository $transactionRepository, private readonly StoreRepository $storeRepository, private readonly Pdf $pdf, private readonly PdfHelper $PDFHelper, private readonly MailerInterface $mailer, private readonly EmailHelper $emailHelper)
    {
    }

    public function __invoke(
        Request               $request,
    ): RedirectResponse
    {
        $year = $request->request->getInt('year', (int)date('Y'));
        $htmlPages = [];
        $stores = $this->storeRepository->findAll();
        foreach ($stores as $store) {
            if ($store->getUserId()) {
                $htmlPages[] = $this->PDFHelper->renderTransactionHtml(
                    $this->transactionRepository,
                    $store,
                    $year
                );
            }
        }

        $fileName = sprintf('movimientos-%d.pdf', $year);
        try {
            $attachment = new DataPart(
                $this->pdf->getOutputFromHtml($htmlPages),
                $fileName,
                'application/pdf'
            );

            $email = $this->emailHelper->createEmail(
                to: $this->emailHelper->getFrom(),
                subject: 'Movimientos de los locales ano ' . $year
            )
                ->text('Backup - Date: ' . date('Y-m-d'))
                ->html('<h3>Backup</h3>Date: ' . date('Y-m-d'))
                ->addPart($attachment);

            $this->mailer->send($email);
            $this->addFlash('success', 'Mail has been sent successfully.');
        } catch (Exception|TransportExceptionInterface $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('welcome');
    }

}
