<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Repository\PaymentMethodRepository;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Repository\TransactionTypeRepository;
use App\Repository\UserRepository;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use UnexpectedValueException;

class AdminController extends AbstractController
{
    /**
     * @Security("is_granted('ROLE_ADMIN')")
     * @throws Exception
     */
    #[Route(path: '/cobrar', name: 'cobrar')]
    public function cobrar(
        StoreRepository $storeRepository,
        UserRepository $userRepository,
        TransactionTypeRepository $transactionTypeRepository,
        Request $request
    ): Response {
        $values = $request->request->get('values');
        $users = $request->request->get('users');
        if ($values) {
            $em = $this->getDoctrine()->getManager();

            // Type "Alquiler"
            $type = $transactionTypeRepository->find(1);

            if (!$type) {
                throw new UnexpectedValueException('Invalid transaction type');
            }

            foreach ($values as $storeId => $value) {
                if (0 === $value) {
                    // No value
                    continue;
                }

                $transaction = (new Transaction)
                    ->setDate(
                        new DateTime($request->request->get('date_cobro'))
                    )
                    ->setStore($storeRepository->find((int)$storeId))
                    ->setUser($userRepository->find((int)$users[$storeId]))
                    ->setType($type)
                    // Set negative value (!)
                    ->setAmount(-$value);

                $em->persist($transaction);
            }

            $em->flush();

            $this->addFlash('success', 'A cobrar...');

            return $this->redirectToRoute('welcome');
        }

        return $this->render(
            'admin/cobrar.html.twig',
            ['stores' => $storeRepository->getActive()]
        );
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     * @throws Exception
     */
    #[Route(path: '/pay-day', name: 'pay-day')]
    public function payDay(
        StoreRepository $storeRepository,
        PaymentMethodRepository $paymentMethodRepository,
        TransactionTypeRepository $transactionTypeRepository,
        Request $request
    ): Response {
        $payments = $request->request->get('payments');
        if (!$payments) {
            return $this->render(
                'admin/payday-html.twig',
                [
                    'stores'         => $storeRepository->getActive(),
                    'paymentMethods' => $paymentMethodRepository->findAll(),
                ]
            );
        }
        $em = $this->getDoctrine()->getManager();
        $type = $transactionTypeRepository->findOneBy(['name' => 'Pago']);
        if (!$type) {
            throw new UnexpectedValueException('Invalid transaction type');
        }
        foreach ($payments['date_cobro'] as $i => $dateCobro) {
            if (!$dateCobro) {
                continue;
            }

            $store = $storeRepository->find((int)$payments['store'][$i]);

            if (!$store) {
                continue;
            }

            $method = $paymentMethodRepository->find(
                (int)$payments['method'][$i]
            );

            if (!$method) {
                throw new UnexpectedValueException('Invalid payment method.');
            }

            $transaction = (new Transaction)
                ->setDate(new DateTime($dateCobro))
                ->setStore($store)
                ->setUser($store->getUser())
                ->setType($type)
                ->setMethod($method)
                ->setRecipeNo((int)$payments['recipe'][$i])
                ->setDocument((int)$payments['document'][$i])
                ->setDepId((int)$payments['depId'][$i])
                ->setAmount($payments['amount'][$i]);

            $em->persist($transaction);
        }
        $em->flush();
        $this->addFlash('success', 'Sa ha pagado...');

        return $this->redirectToRoute('welcome');
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    #[Route(path: '/pagos-por-ano', name: 'pagos-por-ano')]
    public function pagosPorAno(
        Request $request,
        TransactionRepository $repository
    ): Response {
        $year = $request->query->getInt('year', (int)date('Y'));

        return $this->render(
            'admin/pagos-por-ano.html.twig',
            [
                'year'         => $year,
                'transactions' => $repository->getPagosPorAno($year),
            ]
        );
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    #[Route(path: '/mail-list-transactions', name: 'mail-list-transactions')]
    public function mailListTransactions(
        StoreRepository $storeRepository
    ): Response {
        return $this->render(
            'admin/mail-list-transactions.twig',
            [
                'stores' => $storeRepository->getActive(),
            ]
        );
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     */
    #[Route(path: '/mail-list-planillas', name: 'mail-list-planillas')]
    public function mailListPlanillas(
        StoreRepository $storeRepository
    ): Response {
        return $this->render(
            'admin/mail-list-planillas.twig',
            [
                'stores' => $storeRepository->getActive(),
            ]
        );
    }
}
