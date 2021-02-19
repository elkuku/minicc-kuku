<?php

namespace App\Controller\Admin;

use App\Entity\Store;
use App\Entity\Transaction;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\PDFHelper;
use Exception;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use function count;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 */
class TransactionController extends AbstractController
{
    #[Route(path: '/mail-transactions', name: 'mail-transactions', methods: ['POST'])]
    public function mail(
        StoreRepository $storeRepository,
        TransactionRepository $transactionRepository,
        Request $request,
        Pdf $pdf,
        MailerInterface $mailer
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
            if (!array_key_exists($store->getId(), $recipients)) {
                continue;
            }

            $fileName = "movimientos-{$store->getId()}-$year.pdf";
            $html = $this->renderView(
                '_mail/client-transactions.twig',
                [
                    'user'     => $store->getUser(),
                    'store'    => $store,
                    'fileName' => $fileName,
                    'year' => $year,
                ]
            );

            $document = $pdf->getOutputFromHtml(
                $this->getTransactionsHtml(
                    $transactionRepository,
                    $store,
                    $year
                )
            );

            $email = (new Email())
                ->from('minicckuku@gmail.com')
                ->to($store->getUser()->getEmail())
                ->subject("Movimientos del local {$store->getId()} ano $year")
                // ->text('Backup - Date: '.date('Y-m-d'))
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

    #[Route(path: '/store-transaction-pdf/{id}/{year}', name: 'store-transaction-pdf', methods: ['GET'])]
    public function getStore(
        Store $store,
        int $year,
        TransactionRepository $transactionRepository,
        Pdf $pdf,
        PDFHelper $PDFHelper
    ): PdfResponse {
        $this->denyAccessUnlessGranted('export', $store);
        $html = $this->getTransactionsHtml(
            $transactionRepository,
            $store,
            $year
        );
        $filename = sprintf(
            'movimientos-%d-local-%d-%s.pdf',
            $year,
            $store->getId(),
            date('Y-m-d')
        );
        $header = $this->renderView(
            '_header-pdf.html.twig',
            [
                'rootPath' => $PDFHelper->getRoot().'/public',
            ]
        );
        $footer = $this->renderView('_footer-pdf.html.twig');

        return new PdfResponse(
            $pdf->getOutputFromHtml(
                $html,
                [
                    'footer-html' => $footer,
                    'header-html' => $header,
                ]
            ),
            $filename
        );
    }

    #[Route(path: '/mail-annual-transactions', name: 'mail-annual-transactions', methods: ['POST'])]
    public function mailStores(
        Request $request,
        TransactionRepository $transactionRepository,
        StoreRepository $storeRepository,
        Pdf $pdf,
        MailerInterface $mailer
    ): RedirectResponse {
        $year = (int)$request->get('year', date('Y'));
        $htmlPages = [];
        $stores = $storeRepository->findAll();
        foreach ($stores as $store) {
            if ($store->getUserId()) {
                $htmlPages[] = $this->getTransactionsHtml(
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

            $email = (new Email())
                ->from('minicckuku@gmail.com')
                ->to('minicckuku@gmail.com')
                ->subject("Movimientos de los locales ano $year")
                ->text('Backup - Date: '.date('Y-m-d'))
                ->html('<h3>Backup</h3>Date: '.date('Y-m-d'))
                ->attachPart($attachment);

            $mailer->send($email);
            $this->addFlash('success', 'Mail has been sent succesfully.');
        } catch (Exception $exception) {
            $this->addFlash('danger', $exception->getMessage());
        } catch (TransportExceptionInterface $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('welcome');
    }

    #[Route(path: '/stores-transactions-pdf', name: 'stores-transactions-pdf', methods: ['GET'])]
    public function getStores(
        TransactionRepository $transactionRepository,
        StoreRepository $storeRepository,
        Pdf $pdf
    ): PdfResponse {
        $htmlPages = [];
        $year = (int)date('Y');
        $stores = $storeRepository->findAll();
        foreach ($stores as $store) {
            if ($store->getUserId()) {
                $htmlPages[] = $this->getTransactionsHtml(
                    $transactionRepository,
                    $store,
                    $year
                );
            }
        }
        $filename = sprintf('movimientos-%d-%s.pdf', $year, date('Y-m-d'));

        return new PdfResponse(
            $pdf->getOutputFromHtml($htmlPages),
            $filename
        );
    }

    private function getTransactionsHtml(
        TransactionRepository $transactionRepository,
        Store $store,
        int $year,
        int $transactionsPerPage = 42
    ): string {
        $transactions = $transactionRepository->findByStoreAndYear(
            $store,
            $year
        );

        $pages = (int)(count($transactions) / $transactionsPerPage) + 1;
        $fillers = $transactionsPerPage - (count($transactions) - ($pages - 1)
                * $transactionsPerPage);

        for ($i = 1; $i < $fillers; $i++) {
            $transaction = new Transaction;
            $transactions[] = $transaction;
        }

        return $this->renderView(
            '_pdf/transactions-pdf.html.twig',
            [
                'saldoAnterior' => $transactionRepository->getSaldoAnterior(
                    $store,
                    $year
                ),
                'transactions'  => $transactions,
                'store'         => $store,
                'year'          => $year,
            ]
        );
    }
}
