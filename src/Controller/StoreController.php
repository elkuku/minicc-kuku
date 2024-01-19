<?php

namespace App\Controller;

use App\Entity\Store;
use App\Form\StoreType;
use App\Helper\BreadcrumbTrait;
use App\Helper\IntlConverter;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\ChartBuilderService;
use App\Service\TaxService;
use App\Type\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/stores')]
class StoreController extends AbstractController
{
    use BreadcrumbTrait;

    #[IsGranted('ROLE_CASHIER')]
    #[Route(path: '/', name: 'stores-list', methods: ['GET'])]
    public function index(
        StoreRepository $storeRepository
    ): Response {
        return $this->render(
            'stores/list.html.twig',
            [
                'stores' => $storeRepository->findAll(),
            ]
        );
    }

    #[Route(path: '/{id}', name: 'store-transactions', requirements: [
        'id' => '\d+',
    ], methods: [
        'GET',
        'POST',
    ])]
    public function show(
        TransactionRepository $transactionRepository,
        StoreRepository $storeRepository,
        Store $store,
        Request $request,
        TaxService $taxService,
        ChartBuilderService $chartBuilder
    ): Response {
        $this->denyAccessUnlessGranted('view', $store);
        $year = (int)$request->get('year', date('Y'));
        $this->addBreadcrumb('Stores', 'stores-list')
            ->addBreadcrumb('Store '.$store->getId());
        $transactions = $transactionRepository->findByStoreAndYear(
            $store,
            $year
        );
        $headers = [];
        $monthPayments = [];
        $rentalValues = [];
        $rentalValue = $taxService->getValueConTax($store->getValAlq());
        for ($i = 1; $i < 13; $i++) {
            $headers[] = IntlConverter::formatDate('1966-'.$i.'-1', 'MMMM');
            $monthPayments[$i] = 0;
            $rentalValues[$i] = $rentalValue;
        }
        foreach ($transactions as $transaction) {
            if ($transaction->getType() === TransactionType::payment) {
                $monthPayments[$transaction->getDate()->format('n')]
                    += $transaction->getAmount();
            }
        }

        return $this->render(
            'stores/transactions.html.twig',
            [
                'transactions'  => $transactions,
                'saldoAnterior' => $transactionRepository->getSaldoAnterior(
                    $store,
                    $year
                ),
                'headerStr'     => json_encode($headers, JSON_THROW_ON_ERROR),
                'monthPayments' => json_encode(
                    array_values($monthPayments),
                    JSON_THROW_ON_ERROR
                ),
                'rentalValStr'  => json_encode(
                    array_values($rentalValues),
                    JSON_THROW_ON_ERROR
                ),
                'store'         => $store,
                'stores'        => $storeRepository->findAll(),
                'year'          => $year,
                'breadcrumbs'   => $this->getBreadcrumbs(),
                'chart'         => $chartBuilder->getStoreChart(
                    $headers,
                    array_values($monthPayments),
                    array_values($rentalValues)
                ),
            ]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/new', name: 'stores-add', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        TaxService $taxService,
    ): Response {
        $store = new Store;
        $form = $this->createForm(StoreType::class, $store);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $store = $form->getData();

            $entityManager->persist($store);
            $entityManager->flush();

            $this->addFlash('success', 'Store has been saved');

            return $this->redirectToRoute('stores-list');
        }

        return $this->render(
            'stores/form.html.twig',
            [
                'form'          => $form,
                'store'         => $store,
                'ivaMultiplier' => $taxService->getTaxValue(),
            ]
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/edit/{id}', name: 'stores-edit', methods: ['GET', 'POST'])]
    public function edit(
        Store $store,
        Request $request,
        EntityManagerInterface $entityManager,
        TaxService $taxService,
    ): Response {
        $form = $this->createForm(StoreType::class, $store);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $store = $form->getData();

            $entityManager->persist($store);
            $entityManager->flush();

            $this->addFlash('success', 'El local ha sido guardado.');

            return $this->redirectToRoute('stores-list');
        }

        return $this->render(
            'stores/form.html.twig',
            [
                'form'          => $form,
                'store'         => $store,
                'ivaMultiplier' => $taxService->getTaxValue(),
            ]
        );
    }
}
