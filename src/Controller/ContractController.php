<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Form\ContractType;
use App\Repository\ContractRepository;
use App\Repository\StoreRepository;
use App\Repository\UserRepository;
use App\Service\ContractTemplateHelper;
use App\Service\TaxService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: 'contracts')]
class ContractController extends AbstractController
{
    #[Route(path: '/', name: 'contract-list', methods: ['GET', 'POST'])]
    public function list(
        StoreRepository    $storeRepository,
        UserRepository     $userRepository,
        ContractRepository $contractRepository,
        Request            $request
    ): Response
    {
        $storeId = $request->request->getInt('store_id');
        $year = $request->request->getInt('year');

        return $this->render(
            'contract/list.html.twig',
            [
                'stores' => $storeRepository->findAll(),
                'users' => $userRepository->findActiveUsers(),
                'contracts' => $contractRepository->findContracts(
                    $storeId,
                    $year
                ),
                'year' => $year,
                'storeId' => $storeId,
            ]
        );
    }

    #[Route(path: '/new', name: 'contracts-new', methods: ['POST'])]
    public function new(
        StoreRepository        $storeRepo,
        UserRepository         $userRepo,
        ContractRepository     $contractRepo,
        Request                $request,
        EntityManagerInterface $entityManager,
        TaxService             $taxService,
    ): Response
    {
        $store = $storeRepo->find($request->request->getInt('store'));
        $user = $userRepo->find($request->request->getInt('user'));
        $contract = new Contract();
        $contract->setText($contractRepo->findTemplate()->getText());
        if ($store) {
            $contract->setValuesFromStore($store);
        }
        if ($user) {
            $contract
                ->setInqNombreapellido((string)$user->getName())
                ->setInqCi($user->getInqCi());
        }
        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contract = $form->getData();

            $entityManager->persist($contract);
            $entityManager->flush();

            $this->addFlash('success', 'El contrato fue guardado.');

            return $this->redirectToRoute('contract-list');
        }

        return $this->render(
            'contract/form.html.twig',
            [
                'form' => $form,
                'data' => $contract,
                'ivaMultiplier' => $taxService->getTaxValue(),
                'title' => 'Nuevo Contrato',
            ]
        );
    }

    #[Route(
        path: '/{id}',
        name: 'contracts-edit',
        requirements: [
            'id' => '\d+',
        ],
        methods: ['GET', 'POST']
    )]
    public function edit(
        Contract               $contract,
        Request                $request,
        EntityManagerInterface $entityManager,
        TaxService             $taxService,
    ): Response
    {
        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contract = $form->getData();

            $entityManager->persist($contract);
            $entityManager->flush();

            $this->addFlash('success', 'Contrato has been saved');

            return $this->redirectToRoute('contract-list');
        }

        return $this->render(
            'contract/form.html.twig',
            [
                'form' => $form,
                'data' => $contract,
                'ivaMultiplier' => $taxService->getTaxValue(),
                'title' => 'Editar Contrato',
            ]
        );
    }

    #[Route(path: '/delete/{id}', name: 'contracts-delete', methods: ['GET'])]
    public function delete(
        Contract               $contract,
        EntityManagerInterface $entityManager,
    ): RedirectResponse
    {
        $entityManager->remove($contract);
        $entityManager->flush();
        $this->addFlash('success', 'Contract has been deleted');

        return $this->redirectToRoute('contract-list');
    }

    #[Route(path: '/template', name: 'contracts-template', methods: [
        'GET',
        'POST',
    ])]
    public function template(
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

            return $this->redirectToRoute('contract-list');
        }

        return $this->render(
            'contract/form.html.twig',
            [
                'form' => $form,
                'data' => $data,
                'ivaMultiplier' => $taxService->getTaxValue(),
                'title' => 'Plantilla',
            ]
        );
    }

    #[Route(path: '/generate/{id}', name: 'contract-generate', requirements: [
        'id' => '\d+',
    ], methods: ['GET'])]
    public function generate(
        Contract               $contract,
        Pdf                    $pdf,
        ContractTemplateHelper $templateHelper,
    ): PdfResponse
    {
        return new PdfResponse(
            $pdf->getOutputFromHtml(
                $templateHelper->replaceContent($contract),
                ['encoding' => 'utf-8']
            ),
            sprintf(
                'contrato-local-%d-%s.pdf',
                $contract->getStoreNumber(),
                date('Y-m-d')
            )
        );
    }

    #[Route(path: '/get-template-strings', name: 'contract-template-strings', methods: ['GET'])]
    public function getTemplateStrings(ContractTemplateHelper $templateHelper): JsonResponse
    {
        return $this->json($templateHelper->getReplacementStrings());
    }
}
