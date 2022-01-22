<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Repository\PaymentMethodRepository;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Repository\TransactionTypeRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use UnexpectedValueException;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 */
class AdminController extends AbstractController
{
    #[Route(path: '/cobrar', name: 'cobrar', methods: ['GET', 'POST'])]
    public function cobrar(
        StoreRepository $storeRepository,
        UserRepository $userRepository,
        TransactionTypeRepository $transactionTypeRepository,
        PaymentMethodRepository $paymentMethodRepository,
        Request $request,
        ManagerRegistry $managerRegistry,
    ): Response {
        $values = $request->request->all('values');
        if (!$values) {
            return $this->render(
                'admin/cobrar.html.twig',
                ['stores' => $storeRepository->getActive()]
            );
        }

        $users = $request->request->all('users');

        $em = $managerRegistry->getManager();

        // Type "Alquiler"
        $type = $transactionTypeRepository->find(1);
        $method = $paymentMethodRepository->find(1);

        if (!$type || !$method) {
            throw new UnexpectedValueException(
                'Invalid type or payment method.'
            );
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
                ->setMethod($method)
                // Set negative value (!)
                ->setAmount(-$value);

            $em->persist($transaction);
        }

        $em->flush();

        $this->addFlash('success', 'A cobrar...');

        return $this->redirectToRoute('welcome');
    }

    #[Route(path: '/pay-day', name: 'pay-day', methods: ['GET', 'POST'])]
    public function payDay(
        StoreRepository $storeRepository,
        PaymentMethodRepository $paymentMethodRepository,
        TransactionTypeRepository $transactionTypeRepository,
        Request $request,
        ManagerRegistry $managerRegistry,
    ): Response {
        $payments = $request->request->all('payments');
        if (!$payments) {
            return $this->render(
                'admin/payday-html.twig',
                [
                    'stores'         => $storeRepository->getActive(),
                    'paymentMethods' => $paymentMethodRepository->findAll(),
                ]
            );
        }
        $em = $managerRegistry->getManager();
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

    #[Route(path: '/pay-day2', name: 'pay-day2', methods: ['GET', 'POST'])]
    public function payDay2(
        StoreRepository $storeRepository,
        PaymentMethodRepository $paymentMethodRepository,
        TransactionRepository $transactionRepository,
        TransactionTypeRepository $transactionTypeRepository,
        Request $request,
        ManagerRegistry $managerRegistry,
    ): Response {
        $payments = $request->request->all('payments');
        if (!$payments) {
            return $this->render(
                'admin/payday2-html.twig',
                [
                    'stores'         => $storeRepository->getActive(),
                    'lastRecipeNo'   => $transactionRepository->getLastRecipeNo(
                        ) + 1,
                    'paymentMethods' => $paymentMethodRepository->findAll(),
                ]
            );
        }

        $em = $managerRegistry->getManager();
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

    #[Route(path: '/pagos-por-ano', name: 'pagos-por-ano', methods: ['GET'])]
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

    #[Route(path: '/mail-list-transactions', name: 'mail-list-transactions', methods: ['GET'])]
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

    #[Route(path: '/mail-list-planillas', name: 'mail-list-planillas', methods: ['GET'])]
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
