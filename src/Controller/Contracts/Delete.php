<?php
declare(strict_types=1);

namespace App\Controller\Contracts;

use App\Controller\BaseController;
use App\Entity\Contract;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/contracts/delete/{id}', name: 'contracts_delete', methods: ['GET'])]
class Delete extends BaseController
{
    public function __invoke(
        Contract $contract,
        EntityManagerInterface $entityManager,
    ): RedirectResponse
    {
        $entityManager->remove($contract);
        $entityManager->flush();
        $this->addFlash('success', 'Contract has been deleted');

        return $this->redirectToRoute('contracts_index');
    }
}
