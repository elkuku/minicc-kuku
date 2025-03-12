<?php

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
    public function __invoke(
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

        if (!$recipients) {
            return $this->render(
                'mail/planillas-clients.html.twig',
                [
                    'stores' => $storeRepository->getActive(),
                ]
            );
        }

        $year = (int)date('Y');
        $month = (int)date('m');
        $fileName = sprintf('planilla-%d-%s.pdf', $year, $month);
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
                subject: sprintf('Su planilla del local %s (%s - %d)', $store->getId(), $month, $year)
            )
                ->htmlTemplate('email/client-planillas.twig')
                ->context([
                    'user' => $store->getUser(),
                    'store' => $store,
                    'factDate' => sprintf('%d-%s-1', $year, $month),
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
}
