<?php

declare(strict_types=1);

namespace App\Controller\Deposits;

use App\Controller\BaseController;
use App\Entity\Deposit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[IsCsrfTokenValid('deposit_delete', tokenKey: '_token')]
#[Route(path: '/deposits/delete/{id}', name: 'deposits_delete', methods: ['POST'])]
class Delete extends BaseController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(
        Deposit $deposit,
    ): RedirectResponse
    {
        $this->entityManager->remove($deposit);
        $this->entityManager->flush();
        $this->addFlash('success', 'Deposit method has been deleted');

        return $this->redirectToRoute('deposits_index');
    }
}
