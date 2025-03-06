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
    public function __invoke(
        ContractRepository     $contractRepository,
        Request                $request,
        EntityManagerInterface $entityManager,
        TaxService             $taxService,
    ): Response
    {
        $data = $contractRepository->findTemplate();
        $form = $this->createForm(ContractType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
                'ivaMultiplier' => $taxService->getTaxValue(),
                'title' => 'Plantilla',
            ]
        );
    }
}
