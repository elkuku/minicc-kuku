<?php
declare(strict_types=1);

namespace App\Controller\Contracts;

use App\Controller\BaseController;
use App\Entity\Contract;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[IsCsrfTokenValid('contract_delete', tokenKey: '_token')]
#[Route(path: '/contracts/delete/{id}', name: 'contracts_delete', methods: ['POST'])]
class Delete extends BaseController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(
        Contract $contract,
    ): RedirectResponse
    {
        $this->entityManager->remove($contract);
        $this->entityManager->flush();
        $this->addFlash('success', 'Contract has been deleted');

        return $this->redirectToRoute('contracts_index');
    }
}
