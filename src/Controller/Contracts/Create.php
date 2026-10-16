<?php

declare(strict_types=1);

namespace App\Controller\Contracts;

use App\Controller\BaseController;
use App\Dto\ContractCreatePayload;
use App\Entity\Contract;
use App\Form\ContractType;
use App\Repository\ContractRepository;
use App\Repository\StoreRepository;
use App\Repository\UserRepository;
use App\Service\ContractFactory;
use App\Service\TaxService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class Create extends BaseController
{
    public function __construct(
        private readonly StoreRepository $storeRepo,
        private readonly UserRepository $userRepo,
        private readonly ContractRepository $contractRepo,
        private readonly TaxService $taxService,
        private readonly ContractFactory $contractFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route(path: '/contracts/create', name: 'contracts_create', methods: ['POST'])]
    public function new(
        Request $request,
        #[MapRequestPayload] ContractCreatePayload $payload = new ContractCreatePayload(),
    ): Response {
        $store = $this->storeRepo->find($payload->store ?? 0);
        $user = $this->userRepo->find($payload->user ?? 0);
        $template = $this->contractRepo->findTemplate();
        $template = $template instanceof Contract ? $template : null;

        $contract = $store !== null
            ? $this->contractFactory->createFromStore($store, $template)
            : new Contract();

        if ($store === null && $template instanceof Contract) {
            $contract->setText($template->getText());
        }

        if ($user !== null) {
            $contract
                ->setInqNombreapellido((string) $user->getName())
                ->setInqCi($user->getInqCi());
        }

        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($contract);
            $this->entityManager->flush();

            $this->addFlash('success', 'El contrato fue guardado.');

            return $this->redirectToRoute('contracts_index');
        }

        return $this->render(
            'contracts/form.html.twig',
            [
                'form' => $form,
                'data' => $contract,
                'ivaMultiplier' => $this->taxService->getTaxValue(),
                'title' => 'Nuevo Contrato',
            ]
        );
    }
}
