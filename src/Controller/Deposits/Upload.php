<?php

declare(strict_types=1);

namespace App\Controller\Deposits;

use App\Controller\BaseController;
use App\Service\DepositImporter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/deposits/upload', name: 'deposits_upload', methods: ['GET', 'POST'])]
class Upload extends BaseController
{
    public function __invoke(
        Request $request,
        DepositImporter $importer,
    ): RedirectResponse {
        $insertCount = $importer->importFromRequest($request);

        $this->addFlash(
            $insertCount !== 0 ? 'success' : 'warning',
            'Depositos insertados: ' . $insertCount
        );

        return $this->redirectToRoute('deposits_index');
    }
}
