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

/**
 * @Route("/stores")
 */
class StoreController extends AbstractController
{
    use BreadcrumbTrait;

    /**
     * @Route("/", name="stores-list")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function index(StoreRepository $storeRepository): Response
    {
        return $this->render('stores/list.html.twig', ['stores' => $storeRepository->findAll()]);
    }

    /**
     * @Route("/new", name="stores-add")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function new(Request $request): Response
    {
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
     * @Route("/edit/{id}", name="stores-edit")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(Store $store, Request $request): Response
    {
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

    /**
     * @Route("/{id}", name="store-transactions")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function show(
        TransactionRepository $transactionRepository, StoreRepository $storeRepository, Store $store,
        Request $request, TaxService $taxService
    ): Response {
        $year = (int)$request->get('year', date('Y'));

        $this->addBreadcrumb('Stores', 'stores-list')
            ->addBreadcrumb('Store '.$store->getId());

        $transactions = $transactionRepository->findByStoreAndYear($store, $year);

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
                'saldoAnterior' => $transactionRepository->getSaldoAnterior($store, $year),
                'headerStr'     => json_encode($headers),
                'monthPayments' => json_encode(array_values($monthPayments)),
                'rentalValStr'  => json_encode(array_values($rentalValues)),
                'store'         => $store,
                'stores'        => $storeRepository->findAll(),
                'year'          => $year,
                'breadcrumbs'   => $this->getBreadcrumbs(),
            ]
        );
    }
}
