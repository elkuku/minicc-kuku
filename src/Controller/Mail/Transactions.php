<?php

namespace App\Controller\Mail;

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
    public function __invoke(
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

        $fileName = sprintf('movimientos-%d.pdf', $year);
        try {
            $attachment = new DataPart(
                $pdf->getOutputFromHtml($htmlPages),
                $fileName,
                'application/pdf'
            );

            $email = $emailHelper->createEmail(
                to: $emailHelper->getFrom(),
                subject: 'Movimientos de los locales ano ' . $year
            )
                ->text('Backup - Date: ' . date('Y-m-d'))
                ->html('<h3>Backup</h3>Date: ' . date('Y-m-d'))
                ->addPart($attachment);

            $mailer->send($email);
            $this->addFlash('success', 'Mail has been sent successfully.');
        } catch (\Exception|TransportExceptionInterface $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('welcome');
    }

}
