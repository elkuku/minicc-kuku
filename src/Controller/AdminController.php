<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Repository\PaymentMethodRepository;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use App\Type\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminController extends AbstractController
{
    #[Route(path: '/cobrar', name: 'cobrar', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function cobrar(
        StoreRepository $storeRepository,
        UserRepository $userRepository,
        PaymentMethodRepository $paymentMethodRepository,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $values = $request->request->all('values');
        if (! $values) {
            return $this->render(
                'admin/cobrar.html.twig',
                [
                    'stores' => $storeRepository->getActive(),
                ]
            );
        }

        $users = $request->request->all('users');
        $method = $paymentMethodRepository->find(1);

        if (! $method) {
            throw new \UnexpectedValueException('Invalid payment method.');
        }

        foreach ($values as $storeId => $value) {
            if (!$value) {
                continue;
            }

            $user = $userRepository->find((int) $users[$storeId]);

            if (! $user) {
                throw new \UnexpectedValueException('Store has no user.');
            }

            $store = $storeRepository->find((int) $storeId);

            if (! $store) {
                throw new \UnexpectedValueException('Store does not exist.');
            }

            $transaction = (new Transaction())
                ->setDate(
                    new \DateTime((string) $request->request->get('date_cobro'))
                )
                ->setStore($store)
                ->setUser($user)
                ->setType(TransactionType::rent)
                ->setMethod($method)
                // Set negative value (!)
                ->setAmount((string)-$value);

            $entityManager->persist($transaction);
        }

        $entityManager->flush();

        $this->addFlash('success', 'A cobrar...');

        return $this->redirectToRoute('welcome');
    }

    #[Route(path: '/pay-day', name: 'pay-day', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function payDay(
        StoreRepository $storeRepository,
        PaymentMethodRepository $paymentMethodRepository,
        TransactionRepository $transactionRepository,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $payments = $request->request->all('payments');
        if (! $payments) {
            return $this->render(
                'admin/payday-html.twig',
                [
                    'stores' => $storeRepository->getActive(),
                    'lastRecipeNo' => $transactionRepository->getLastRecipeNo() + 1,
                    'paymentMethods' => $paymentMethodRepository->findAll(),
                ]
            );
        }

        foreach ($payments['date_cobro'] as $i => $dateCobro) {
            if (! $dateCobro) {
                continue;
            }

            $store = $storeRepository->find((int) $payments['store'][$i]);

            if (! $store) {
                continue;
            }

            $method = $paymentMethodRepository->find((int) $payments['method'][$i]);

            if (! $method) {
                throw new \UnexpectedValueException('Invalid payment method.');
            }

            $user = $store->getUser();

            if (! $user) {
                throw new \UnexpectedValueException('Store has no user.');
            }

            $transaction = (new Transaction())
                ->setDate(new \DateTime($dateCobro))
                ->setStore($store)
                ->setUser($user)
                ->setType(TransactionType::payment)
                ->setMethod($method)
                ->setRecipeNo((int) $payments['recipe'][$i])
                ->setDocument((int) $payments['document'][$i])
                ->setDepId((int) $payments['depId'][$i])
                ->setAmount($payments['amount'][$i])
                ->setComment($payments['comment'][$i]);

            $entityManager->persist($transaction);
        }
        $entityManager->flush();
        $this->addFlash('success', 'Sa ha pagado...');

        return $this->redirectToRoute('welcome');
    }

    #[Route(path: '/pay-day2', name: 'pay-day2', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function payDay2(
        StoreRepository $storeRepository,
        PaymentMethodRepository $paymentMethodRepository,
        TransactionRepository $transactionRepository,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $payments = $request->request->all('payments');
        if (! $payments) {
            return $this->render(
                'admin/payday2.html.twig',
                [
                    'stores' => $storeRepository->getActive(),
                    'lastRecipeNo' => $transactionRepository->getLastRecipeNo() + 1,
                    'paymentMethods' => $paymentMethodRepository->findAll(),
                ]
            );
        }

        foreach ($payments['date_cobro'] as $i => $dateCobro) {
            if (! $dateCobro) {
                continue;
            }

            $store = $storeRepository->find((int) $payments['store'][$i]);

            if (! $store) {
                continue;
            }

            $method = $paymentMethodRepository->find((int) $payments['method'][$i]);

            if (! $method) {
                throw new \UnexpectedValueException('Invalid payment method.');
            }

            $user = $store->getUser();

            if (! $user) {
                throw new \UnexpectedValueException('Store has no user.');
            }

            $transaction = (new Transaction())
                ->setDate(new \DateTime($dateCobro))
                ->setStore($store)
                ->setUser($user)
                ->setType(TransactionType::payment)
                ->setMethod($method)
                ->setRecipeNo((int) $payments['recipe'][$i])
                ->setDocument((int) $payments['document'][$i])
                ->setDepId((int) $payments['depId'][$i])
                ->setAmount($payments['amount'][$i])
                ->setComment($payments['comment'][$i]);

            $entityManager->persist($transaction);
        }
        $entityManager->flush();
        $this->addFlash('success', 'Sa ha pagado...');

        return $this->redirectToRoute('welcome');
    }

    #[Route(path: '/pagos-por-ano', name: 'pagos-por-ano', methods: ['GET'])]
    #[IsGranted('ROLE_CASHIER')]
    public function pagosPorAno(
        Request $request,
        TransactionRepository $repository
    ): Response {
        $year = $request->query->getInt('year', (int) date('Y'));
        $month = $year === (int) date('Y') ? (int) date('m') : 1;

        return $this->render(
            'admin/pagos-por-ano.html.twig',
            [
                'year' => $year,
                'month' => $month,
                'transactions' => $repository->getPagosPorAno($year),
            ]
        );
    }

    #[Route(path: '/mail-list-transactions', name: 'mail-list-transactions', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
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
    #[IsGranted('ROLE_ADMIN')]
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
