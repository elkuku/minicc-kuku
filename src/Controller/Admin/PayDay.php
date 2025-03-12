<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Transaction;
use App\Repository\PaymentMethodRepository;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Type\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route(path: '/admin/pay-day', name: 'admin_pay_day', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_ADMIN')]
class PayDay extends BaseController
{
    public function __invoke(
        StoreRepository         $storeRepository,
        PaymentMethodRepository $paymentMethodRepository,
        TransactionRepository   $transactionRepository,
        Request                 $request,
        EntityManagerInterface  $entityManager,
    ): Response
    {
        $payments = $request->request->all('payments');
        if (!$payments) {
            $paymentMethods = $paymentMethodRepository->findAll();
            $serializer = new Serializer([new GetSetMethodNormalizer()], ['json' => new JsonEncoder()]);
            return $this->render(
                'admin/payday.html.twig',
                [
                    'stores' => $storeRepository->getActive(),
                    'lastRecipeNo' => $transactionRepository->getLastRecipeNo() + 1,
                    'paymentMethods' => $paymentMethods,
                    'paymentMethodsString' => $serializer->serialize($paymentMethods, 'json'),
                ]
            );
        }

        foreach ($payments['date'] as $i => $dateCobro) {
            if (!$dateCobro) {
                continue;
            }

            $store = $storeRepository->find((int)$payments['store'][$i]);

            if (!$store) {
                continue;
            }

            $method = $paymentMethodRepository->find((int)$payments['method'][$i]);

            if (!$method) {
                throw new \UnexpectedValueException('Invalid payment method.');
            }

            $user = $store->getUser();

            if (!$user) {
                throw new \UnexpectedValueException('Store has no user.');
            }

            $transaction = (new Transaction())
                ->setDate(new \DateTime($dateCobro))
                ->setStore($store)
                ->setUser($user)
                ->setType(TransactionType::payment)
                ->setMethod($method)
                ->setRecipeNo((int)$payments['recipe'][$i])
                ->setDocument((int)$payments['document'][$i])
                ->setDepId((int)$payments['deposit'][$i])
                ->setAmount($payments['amount'][$i])
                ->setComment($payments['comment'][$i]);

            $entityManager->persist($transaction);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Sa ha pagado...');

        return $this->redirectToRoute('welcome');
    }
}
