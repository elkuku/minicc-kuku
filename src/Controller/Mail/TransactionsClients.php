<?php

declare(strict_types=1);

namespace App\Controller\Mail;

use App\Controller\BaseController;
use App\Entity\Store;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\BulkMailService;
use App\Service\EmailHelper;
use App\Service\MailBatchResult;
use App\Service\PdfHelper;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/mail/transactions-clients', name: 'mail_transactions_clients', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_ADMIN')]
class TransactionsClients extends BaseController
{
    public function __construct(
        private readonly StoreRepository $storeRepository,
        private readonly TransactionRepository $transactionRepository,
        private readonly PdfHelper $PDFHelper,
        private readonly EmailHelper $emailHelper,
        private readonly BulkMailService $bulkMailService,
        private readonly ClockInterface $clock,
    ) {}

    public function __invoke(Request $request): Response
    {
        $recipients = $request->request->all('recipients');

        if ($recipients === []) {
            return $this->render('mail/transactions-clients.html.twig', [
                'stores' => $this->storeRepository->getActive(),
                'years' => range((int) $this->clock->now()->format('Y'), (int) $this->clock->now()->modify('-5 years')->format('Y')),
            ]);
        }

        $year = $request->request->getInt('year', (int) $this->clock->now()->format('Y'));

        $result = $this->bulkMailService->sendToFilteredStores(
            $this->storeRepository->getActive(),
            $recipients,
            function (Store $store) use ($year): Email {
                $fileName = sprintf('movimientos-%s-%d.pdf', $store->getId(), $year);

                $document = $this->PDFHelper->renderTransactionsPdf(
                    $this->PDFHelper->renderTransactionHtml($this->transactionRepository, $store, $year)
                );

                return $this->emailHelper->createTemplatedEmail(
                    to: new Address((string) $store->getUser()?->getEmail(), (string) $store->getUser()?->getName()),
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
            }
        );

        $this->flashBatchResult($result);

        return $this->redirectToRoute('welcome');
    }

    private function flashBatchResult(MailBatchResult $result): void
    {
        if ($result->hasFailures()) {
            $this->addFlash('warning', implode('<br>', $result->getFailures()));
        }

        if ($result->hasSuccesses()) {
            $this->addFlash('success', 'Mails have been sent to stores: '.implode(', ', $result->getSuccesses()));
        }
    }
}
