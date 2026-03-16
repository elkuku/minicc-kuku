<?php

declare(strict_types=1);

namespace App\Controller\Mail;

use App\Controller\BaseController;
use App\Entity\Store;
use App\Repository\StoreRepository;
use App\Service\BulkMailService;
use App\Service\EmailHelper;
use App\Service\MailBatchResult;
use App\Service\PayrollHelper;
use App\Service\PdfHelper;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/mail/planillas-clients', name: 'mail_planillas_clients', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_ADMIN')]
class PlanillasClients extends BaseController
{
    public function __construct(
        private readonly StoreRepository $storeRepository,
        private readonly PdfHelper $PDFHelper,
        private readonly EmailHelper $emailHelper,
        private readonly PayrollHelper $payrollHelper,
        private readonly BulkMailService $bulkMailService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $recipients = $request->request->all('recipients');

        if ($recipients === []) {
            return $this->render('mail/planillas-clients.html.twig', [
                'stores' => $this->storeRepository->getActive(),
            ]);
        }

        $year = (int) date('Y');
        $month = (int) date('m');
        $fileName = sprintf('planilla-%d-%s.pdf', $year, $month);

        $result = $this->bulkMailService->sendToFilteredStores(
            $this->storeRepository->getActive(),
            $recipients,
            function (Store $store) use ($year, $month, $fileName): Email {
                $document = $this->PDFHelper->renderPayrollPdf($year, $month, $this->payrollHelper, (int) $store->getId());

                return $this->emailHelper->createTemplatedEmail(
                    to: new Address($store->getUser()?->getEmail() ?? '', $store->getUser()?->getName() ?? ''),
                    subject: sprintf('Su planilla del local %s (%s - %d)', $store->getId(), $month, $year)
                )
                    ->htmlTemplate('email/client-planillas.twig')
                    ->context([
                        'user' => $store->getUser(),
                        'store' => $store,
                        'factDate' => sprintf('%d-%s-1', $year, $month),
                        'fileName' => $fileName,
                        'payroll' => $this->payrollHelper->getData($year, $month, (int) $store->getId()),
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
