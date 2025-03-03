<?php

namespace App\Controller\Download;

use App\Controller\BaseController;
use App\Service\PayrollHelper;
use App\Service\PdfHelper;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/download/planillas', name: 'download_planillas', methods: ['GET'])]
class Planillas extends BaseController
{
    public function __invoke(
        PdfHelper $PdfHelper,
        PayrollHelper $payrollHelper,
    ): PdfResponse {
        $year = (int) date('Y');
        $month = (int) date('m');
        $filename = sprintf('payrolls-%d-%d.pdf', $year, $month);

        $html = $PdfHelper->renderPayrollsHtml($year, $month, $payrollHelper);

        return new PdfResponse(
            $PdfHelper->getOutputFromHtml(
                $html,
                [
                    'enable-local-file-access' => true,
                ]
            ),
            $filename
        );
    }

}
