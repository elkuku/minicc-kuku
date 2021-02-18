<?php

namespace App\Controller;

use App\Entity\Store;
use App\Form\StoreType;
use App\Helper\BreadcrumbTrait;
use App\Helper\IntlConverter;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\TaxService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route(path: '/stores')]
class StoreController extends AbstractController
{
    use BreadcrumbTrait;

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    #[Route(path: '/', name: 'stores-list')]
    public function index(
        StoreRepository $storeRepository
    ): Response {
        return $this->render(
            'stores/list.html.twig',
            ['stores' => $storeRepository->findAll()]
        );
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    #[Route(path: '/new', name: 'stores-add')]
    public function new(
        Request $request
    ): Response {
        $store = new Store;
        $form = $this->createForm(StoreType::class, $store);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $store = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($store);
            $em->flush();

            $this->addFlash('success', 'Store has been saved');

            return $this->redirectToRoute('stores-list');
        }

        return $this->render(
            'stores/form.html.twig',
            [
                'form'          => $form->createView(),
                'store'         => $store,
                'ivaMultiplier' => $_ENV['value_iva'],
            ]
        );
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    #[Route(path: '/edit/{id}', name: 'stores-edit')]
    public function edit(
        Store $store,
        Request $request
    ): Response {
        $form = $this->createForm(StoreType::class, $store);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $store = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($store);
            $em->flush();

            $this->addFlash('success', 'El local ha sido guardado.');

            return $this->redirectToRoute('stores-list');
        }

        return $this->render(
            'stores/form.html.twig',
            [
                'form'          => $form->createView(),
                'store'         => $store,
                'ivaMultiplier' => $_ENV['value_iva'],
            ]
        );
    }

    #[Route(path: '/{id}', name: 'store-transactions')]
    public function show(
        TransactionRepository $transactionRepository,
        StoreRepository $storeRepository,
        Store $store,
        Request $request,
        TaxService $taxService,
        ChartBuilderInterface $chartBuilder
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
            if ($transaction->getType()->getName() === 'Pago') {
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
                'headerStr'     => json_encode($headers),
                'monthPayments' => json_encode(array_values($monthPayments)),
                'rentalValStr'  => json_encode(array_values($rentalValues)),
                'store'         => $store,
                'stores'        => $storeRepository->findAll(),
                'year'          => $year,
                'breadcrumbs'   => $this->getBreadcrumbs(),
                'chart'         => $this->getChart(
                    $headers,
                    array_values($monthPayments),
                    array_values($rentalValues),
                    $chartBuilder
                ),
            ]
        );
    }

    private function getChart(
        array $labels,
        array $dataPayments,
        array $dataRent,
        ChartBuilderInterface $chartBuilder
    ): Chart {
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData(
            [
                'labels'   => $labels,
                'datasets' => [
                    [
                        'label'           => 'Pagos',
                        'data'            => $dataPayments,
                        'fill'            => 'false',
                        'lineTension'     => 0.1,
                        'backgroundColor' => 'rgba(75,192,192,0.4)',
                        'borderColor'     => 'rgba(75,192,192,1)',
                    ],
                    [
                        'label'           => 'Alquiler',
                        'data'            => $dataRent,
                        'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                        'borderColor'     => 'rgba(255, 206, 86, 0.2)',
                        'borderWidth'     => 1,
                    ],
                ],
            ]
        );

        return $chart;
    }
}
