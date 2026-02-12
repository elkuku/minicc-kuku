<?php

declare(strict_types=1);

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
#[Route(path: 'stores/create', name: 'stores_create', methods: ['GET', 'POST'])]
class Create extends BaseController
{
    public function __construct(private readonly TaxService $taxService)
    {
    }

    public function __invoke(
        Request                $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $store = new Store();
        $form = $this->createForm(StoreType::class, $store);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $store = $form->getData();

            $entityManager->persist($store);
            $entityManager->flush();

            $this->addFlash('success', 'Store has been saved');

            return $this->redirectToRoute('stores_index');
        }

        return $this->render(
            'stores/form.html.twig',
            [
                'form' => $form,
                'store' => $store,
                'ivaMultiplier' => $this->taxService->getTaxValue(),
            ]
        );
    }
}
