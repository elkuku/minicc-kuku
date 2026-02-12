<?php
declare(strict_types=1);

namespace App\Controller\Contracts;

use App\Controller\BaseController;
use App\Form\ContractType;
use App\Repository\ContractRepository;
use App\Service\TaxService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/contracts/template', name: 'contracts_template', methods: ['GET', 'POST',])]
class Template extends BaseController
{
    public function __construct(
        private readonly ContractRepository $contractRepository,
        private readonly TaxService $taxService
    ) {}

    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $data = $this->contractRepository->findTemplate();
        $form = $this->createForm(ContractType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\Contract $data */
            $data = $form->getData();
            $entityManager->persist($data);
            $entityManager->flush();

            $this->addFlash('success', 'Template has been saved');

            return $this->redirectToRoute('contracts_index');
        }

        return $this->render(
            'contracts/form.html.twig',
            [
                'form' => $form,
                'data' => $data,
                'ivaMultiplier' => $this->taxService->getTaxValue(),
                'title' => 'Plantilla',
            ]
        );
    }
}
