<?php

declare(strict_types=1);

namespace App\Controller\Contracts;

use App\Controller\BaseController;
use App\Service\ContractTemplateHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/contracts/template-strings', name: 'contracts_template_strings', methods: ['GET'])]
class TemplateStrings extends BaseController
{
    public function __construct(private readonly ContractTemplateHelper $templateHelper) {}

    public function __invoke(): JsonResponse
    {
        return $this->json($this->templateHelper->getReplacementStrings());
    }
}
