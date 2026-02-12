<?php

declare(strict_types=1);

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
    public function __construct(private readonly PdfHelper $PdfHelper, private readonly PayrollHelper $payrollHelper)
    {
    }

    public function __invoke(): PdfResponse
    {
        $year = (int)date('Y');
        $month = (int)date('m');
        $filename = sprintf('payrolls-%d-%d.pdf', $year, $month);
        $html = $this->PdfHelper->renderPayrollsHtml($year, $month, $this->payrollHelper);
        return new PdfResponse(
            $this->PdfHelper->getOutputFromHtml(
                $html,
                [
                    'enable-local-file-access' => true,
                ]
            ),
            $filename
        );
    }

}
