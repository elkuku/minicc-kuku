<?php
declare(strict_types=1);

namespace App\Controller\Contracts;

use App\Controller\BaseController;
use App\Entity\Contract;
use App\Form\ContractType;
use App\Service\TaxService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/contracts/edit/{id}', name: 'contracts_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
class Edit extends BaseController
{
    public function __construct(private readonly TaxService $taxService) {}

    public function __invoke(
        Contract $contract,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contract = $form->getData();

            $entityManager->persist($contract);
            $entityManager->flush();

            $this->addFlash('success', 'Contrato has been saved');

            return $this->redirectToRoute('contracts_index');
        }

        return $this->render(
            'contracts/form.html.twig',
            [
                'form' => $form,
                'data' => $contract,
                'ivaMultiplier' => $this->taxService->getTaxValue(),
                'title' => 'Editar Contrato',
            ]
        );
    }
}
