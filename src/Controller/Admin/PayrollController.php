<?php

namespace App\Controller\Admin;

use App\Repository\StoreRepository;
use App\Service\PayrollHelper;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 */
class PayrollController extends AbstractController
{
    #[Route(path: '/planillas-mail', name: 'planillas-mail', methods: ['GET'])]
    public function mail(
        Pdf $pdf,
        MailerInterface $mailer,
        PayrollHelper $payrollHelper,
    ): Response {
        $year = date('Y');
        $month = date('m');
        $fileName = "payrolls-$year-$month.pdf";
        $html = 'Attachment: '.$fileName;
        $document = $pdf->getOutputFromHtml(
            $this->renderPayrolls(
                $year,
                $month,
                $payrollHelper
            ),
            ['enable-local-file-access' => true]
        );
        $email = (new Email())
            ->from('minicckuku@gmail.com')
            ->to('minicckuku@gmail.com')
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
    public function mailClients(
        StoreRepository $storeRepository,
        Request $request,
        Pdf $pdf,
        MailerInterface $mailer,
        PayrollHelper $payrollHelper
    ): Response {
        $recipients = $request->get('recipients');
        if (!$recipients) {
            $this->addFlash('warning', 'No recipients selected');

            return $this->redirectToRoute('mail-list-transactions');
        }
        $year = date('Y');
        $month = date('m');
        $fileName = "planilla-$year-$month.pdf";
        $stores = $storeRepository->getActive();
        $failures = [];
        $successes = [];

        foreach ($stores as $store) {
            if (!array_key_exists($store->getId(), $recipients)) {
                continue;
            }

            $document = $pdf->getOutputFromHtml(
                $this->renderPayrolls(
                    $year,
                    $month,
                    $payrollHelper,
                    $store->getId()
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

            $email = (new Email())
                ->from('minicckuku@gmail.com')
                ->to($user->getEmail())
                ->subject(
                    "Su planilla del local {$store->getId()} ($month - $year)"
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

    #[Route(path: '/planillas', name: 'planillas', methods: ['GET'])]
    public function download(
        Pdf $pdf,
        PayrollHelper $payrollHelper,
    ): PdfResponse {
        $year = (int)date('Y');
        $month = (int)date('m');
        $filename = sprintf('payrolls-%d-%d.pdf', $year, $month);

        $html = $this->renderPayrolls($year, $month, $payrollHelper);

        return new PdfResponse(
            $pdf->getOutputFromHtml(
                $html,
                ['enable-local-file-access' => true]
            ),
            $filename
        );
    }

    private function renderPayrolls(
        int $year,
        int $month,
        PayrollHelper $payrollHelper,
        int $storeId = 0
    ): string {
        return $this->renderView(
            '_pdf/payrolls-pdf.html.twig',
            $payrollHelper->getData($year, $month, $storeId)
        );
    }
}
