<?php

declare(strict_types=1);

namespace App\Controller\Download;

use App\Controller\BaseController;
use App\Service\PayrollHelper;
use App\Service\PdfHelper;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/download/planillas', name: 'download_planillas', methods: ['GET'])]
class Planillas extends BaseController
{
    public function __construct(
        private readonly PdfHelper $pdfHelper,
        private readonly PayrollHelper $payrollHelper,
        private readonly ClockInterface $clock,
    ) {}

    public function __invoke(): PdfResponse
    {
        $now = $this->clock->now();
        $year = (int) $now->format('Y');
        $month = (int) $now->format('m');
        $filename = sprintf('payrolls-%d-%d.pdf', $year, $month);

        return new PdfResponse($this->pdfHelper->renderPayrollPdf($year, $month, $this->payrollHelper), $filename);
    }
}
