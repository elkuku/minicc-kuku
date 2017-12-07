<?php

namespace App\Controller;

use App\Entity\Store;
use App\Entity\Transaction;
use App\Form\StoreType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class StoreController
 */
class StoreController extends AbstractController
{
    /**
     * @Route("/stores", name="stores-list")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $stores = $this->getDoctrine()
            ->getRepository(Store::class)
            ->findAll();

        return $this->render('stores/list.html.twig', ['stores' => $stores]);
    }

    /**
     * @Route("/store-add", name="stores-add")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        $store = new Store();
        $form  = $this->createForm(StoreType::class, $store);

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
                'ivaMultiplier' => getenv('value_iva'),
            ]
        );
    }

    /**
     * @Route("/store-edit/{id}", name="stores-edit")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Store   $store
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Store $store, Request $request)
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
                'ivaMultiplier' => getenv('value_iva'),
            ]
        );
    }

    /**
     * @Route("/store/{id}", name="store-transactions")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Store   $store
     * @param Request $request
     *
     * @return Response
     */
    public function listTransactionsAction(Store $store, Request $request)
    {
        $year = (int) $request->get('year', date('Y'));

        $this->addBreadcrumb('Locales', 'stores-list')
            ->addBreadcrumb('Local '.$store->getId());

        $transactionRepo = $this->getDoctrine()
            ->getRepository(Transaction::class);

        $transactions = $transactionRepo->findByStoreAndYear($store, $year);

        $monthPayments = [];
        for ($i = 1; $i < 13; $i++) {
            $monthPayments[$i] = 0;
        }

        /* @type Transaction $transaction */
        foreach ($transactions as $transaction) {
            if ($transaction->getType()->getName() == 'Pago') {
                $monthPayments[$transaction->getDate()->format('n')] += $transaction->getAmount();
            }
        }

        $monthPayments = implode(', ', $monthPayments);

        return $this->render(
            'stores/transactions.html.twig',
            [
                'transactions'  => $transactions,
                'saldoAnterior' => $transactionRepo->getSaldoAnterior($store, $year),
                'monthPayments' => $monthPayments,
                'store'         => $store,
                'stores'        => $this->getDoctrine()->getRepository(Store::class)->findAll(),
                'year'          => $year,
                'breadcrumbs'   => $this->getBreadcrumbs(),
            ]
        );
    }

    /**
     * @Route("/store-transaction-pdf/{id}/{year}", name="store-transaction-pdf")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function transactionPdfAction(Request $request)
    {
        $storeId = (int) $request->get('id');
        $year    = (int) $request->get('year', date('Y'));
        $transactionsPerPage = 42;

        $transactionRepo = $this->getDoctrine()
            ->getRepository(Transaction::class);

        $store = $this->getDoctrine()
            ->getRepository(Store::class)
            ->find($storeId);

        $transactions = $transactionRepo->findByStoreAndYear($store, $year);

        $pages = intval(count($transactions) / $transactionsPerPage) + 1;
        $fillers = $transactionsPerPage - (count($transactions) - ($pages - 1) * $transactionsPerPage);

        for ($i = 1; $i < $fillers; $i++) {
            $transaction = new Transaction();
            $transactions[] = $transaction;
        }

        $html = $this->renderView(
            'stores/transactions-pdf.html.twig',
            [
                'saldoAnterior' => $transactionRepo->getSaldoAnterior($store, $year),
                'transactions'  => $transactions,
                'store'         => $store,
                'year'          => $year,
            ]
        );

        $filename = sprintf('movimientos-%d-local-%d-%s.pdf', $year, $storeId, date('Y-m-d'));

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            ]
        );
    }
}
