<?php

declare(strict_types=1);

namespace App\Controller\Mail;

use App\Controller\BaseController;
use App\Service\EmailHelper;
use App\Service\PayrollHelper;
use App\Service\PdfHelper;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class Planillas extends BaseController
{
    public function __construct(private readonly Pdf $pdf, private readonly PdfHelper $PDFHelper, private readonly MailerInterface $mailer, private readonly EmailHelper $emailHelper, private readonly PayrollHelper $payrollHelper) {}

    #[Route(path: '/mail/planillas', name: 'mail_planillas', methods: ['GET'])]
    public function planillas(): Response
    {
        $year = (int)date('Y');
        $month = (int)date('m');
        $fileName = sprintf('payrolls-%d-%s.pdf', $year, $month);
        $html = 'Attachment: '.$fileName;
        $document = $this->pdf->getOutputFromHtml(
            $this->PDFHelper->renderPayrollsHtml(
                $year,
                $month,
                $this->payrollHelper
            ),
            [
                'enable-local-file-access' => true,
            ]
        );
        $email = $this->emailHelper->createEmail(
            to: $this->emailHelper->getFrom(),
            subject: sprintf('Planillas %d-%s', $year, $month)
        )
            ->subject(sprintf('Planillas %d-%s', $year, $month))
            ->html($html)
            ->attach($document, $fileName);
        try {
            $this->mailer->send($email);
            $this->addFlash('success', 'Mail has been sent.');
        } catch (TransportExceptionInterface $transportException) {
            $this->addFlash('danger', 'ERROR sending mail: '.$transportException->getMessage());
        }

        return $this->render('admin/tasks.html.twig');
    }
}
