<?php

declare(strict_types=1);

namespace App\Controller\Deposits;

use App\Controller\BaseController;
use App\Service\DepositImporter;
use DateMalformedStringException;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UnexpectedValueException;

#[IsGranted('ROLE_ADMIN')]
#[IsCsrfTokenValid('deposit_upload', tokenKey: '_token')]
#[Route(path: '/deposits/upload', name: 'deposits_upload', methods: ['GET', 'POST'])]
class Upload extends BaseController
{
    public function __construct(private readonly DepositImporter $importer)
    {
    }

    public function __invoke(
        Request $request,
    ): RedirectResponse {
        try {
            $insertCount = $this->importer->importFromRequest($request);
        } catch (RuntimeException|UnexpectedValueException|DateMalformedStringException $e) {
            $this->addFlash('danger', 'No se pudo importar el archivo: ' . $e->getMessage());

            return $this->redirectToRoute('deposits_index');
        }

        $this->addFlash(
            $insertCount !== 0 ? 'success' : 'warning',
            'Depositos insertados: ' . $insertCount
        );

        return $this->redirectToRoute('deposits_index');
    }
}
