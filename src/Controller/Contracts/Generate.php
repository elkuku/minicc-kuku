<?php
declare(strict_types=1);

namespace App\Controller\Contracts;

use App\Controller\BaseController;
use App\Entity\Contract;
use App\Service\ContractTemplateHelper;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/contracts/generate/{id}', name: 'contracts_generate', requirements: ['id' => '\d+',], methods: ['GET'])]
class Generate extends BaseController
{
    public function __construct(private readonly Pdf $pdf, private readonly ContractTemplateHelper $templateHelper) {}

    public function __invoke(Contract $contract): PdfResponse
    {
        return new PdfResponse(
            $this->pdf->getOutputFromHtml(
                $this->templateHelper->replaceContent($contract),
                ['encoding' => 'utf-8']
            ),
            sprintf(
                'contrato-local-%d-%s.pdf',
                $contract->getStoreNumber(),
                date('Y-m-d')
            )
        );
    }
}
