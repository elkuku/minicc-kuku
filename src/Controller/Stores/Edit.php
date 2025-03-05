<?php

namespace App\Controller\Stores;

use App\Controller\BaseController;
use App\Entity\Store;
use App\Form\StoreType;
use App\Service\TaxService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/stores/edit/{id}', name: 'stores_edit', methods: ['GET', 'POST'])]
class Edit extends BaseController
{
    public function __invoke(
        Store                  $store,
        Request                $request,
        EntityManagerInterface $entityManager,
        TaxService             $taxService,
    ): Response
    {
        $form = $this->createForm(StoreType::class, $store);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $store = $form->getData();

            $entityManager->persist($store);
            $entityManager->flush();

            $this->addFlash('success', 'El local ha sido guardado.');

            return $this->redirectToRoute('stores_index');
        }

        return $this->render(
            'stores/form.html.twig',
            [
                'form' => $form,
                'store' => $store,
                'ivaMultiplier' => $taxService->getTaxValue(),
            ]
        );
    }
}
