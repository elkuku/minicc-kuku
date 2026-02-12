<?php

declare(strict_types=1);

namespace App\Controller\Mail;

use App\Controller\BaseController;
use App\Repository\StoreRepository;
use App\Service\EmailHelper;
use App\Service\PayrollHelper;
use App\Service\PdfHelper;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/mail/planillas-clients', name: 'mail_planillas_clients', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_ADMIN')]
class PlanillasClients extends BaseController
{
    public function __construct(private readonly StoreRepository $storeRepository, private readonly Pdf $pdf, private readonly PdfHelper $PDFHelper, private readonly MailerInterface $mailer, private readonly EmailHelper $emailHelper, private readonly PayrollHelper $payrollHelper) {}

    public function __invoke(
        Request $request
    ): Response
    {
        $recipients = $request->request->all('recipients');

        if ($recipients === []) {
            return $this->render(
                'mail/planillas-clients.html.twig',
                [
                    'stores' => $this->storeRepository->getActive(),
                ]
            );
        }

        $year = (int)date('Y');
        $month = (int)date('m');
        $fileName = sprintf('planilla-%d-%s.pdf', $year, $month);
        $stores = $this->storeRepository->getActive();
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

            $document = $this->pdf->getOutputFromHtml(
                $this->PDFHelper->renderPayrollsHtml(
                    $year,
                    $month,
                    $this->payrollHelper,
                    (int)$store->getId()
                ),
                [
                    'enable-local-file-access' => true,
                ]
            );

            $email = $this->emailHelper->createTemplatedEmail(
                to: new Address($user->getEmail(), $user->getName() ?? ''),
                subject: sprintf('Su planilla del local %s (%s - %d)', $store->getId(), $month, $year)
            )
                ->htmlTemplate('email/client-planillas.twig')
                ->context([
                    'user' => $store->getUser(),
                    'store' => $store,
                    'factDate' => sprintf('%d-%s-1', $year, $month),
                    'fileName' => $fileName,
                    'payroll' => $this->payrollHelper->getData($year, $month, (int)$store->getId()),
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
