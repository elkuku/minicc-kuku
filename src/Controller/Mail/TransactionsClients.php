<?php

declare(strict_types=1);

namespace App\Controller\Mail;

use App\Controller\BaseController;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\EmailHelper;
use App\Service\PdfHelper;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/mail/transactions-clients', name: 'mail_transactions_clients', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_ADMIN')]
class TransactionsClients extends BaseController
{
    public function __construct(private readonly StoreRepository $storeRepository, private readonly TransactionRepository $transactionRepository, private readonly Pdf $pdf, private readonly PdfHelper $PDFHelper, private readonly MailerInterface $mailer, private readonly EmailHelper $emailHelper) {}

    public function __invoke(
        Request $request,
    ): Response
    {
        $recipients = $request->request->all('recipients');

        if ($recipients === []) {
            return $this->render(
                'mail/transactions-clients.html.twig',
                [
                    'stores' => $this->storeRepository->getActive(),
                    'years' => range(date('Y'), date('Y', strtotime('-5 year'))),
                ]
            );
        }

        $year = $request->request->getInt('year', (int)date('Y'));
        $stores = $this->storeRepository->getActive();
        $failures = [];
        $successes = [];

        foreach ($stores as $store) {
            if (!array_key_exists((int)$store->getId(), $recipients)) {
                continue;
            }

            $fileName = sprintf('movimientos-%s-%d.pdf', $store->getId(), $year);

            $document = $this->pdf->getOutputFromHtml(
                $this->PDFHelper->renderTransactionHtml(
                    $this->transactionRepository,
                    $store,
                    $year
                )
            );

            $email = $this->emailHelper
                ->createTemplatedEmail(
                    to: new Address((string)$store->getUser()?->getEmail(), (string)$store->getUser()?->getName()),
                    subject: sprintf('Movimientos del local %s ano %d', $store->getId(), $year)
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
                $this->mailer->send($email);
                $successes[] = $store->getId();
            } catch (TransportExceptionInterface $exception) {
                $failures[] = $exception->getMessage();
            }
        }

        if ($failures !== []) {
            $this->addFlash('warning', implode('<br>', $failures));
        }

        if ($successes !== []) {
            $this->addFlash(
                'success',
                'Mails have been sent to stores: '
                .implode(', ', $successes)
            );
        }

        return $this->redirectToRoute('welcome');
    }
}
