<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Dto\PayDayPayload;
use App\Dto\PayDayPayments;
use App\Entity\Transaction;
use App\Repository\PaymentMethodRepository;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Service\TransactionFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use UnexpectedValueException;

#[Route(path: '/admin/pay-day', name: 'admin_pay_day', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_ADMIN')]
class PayDay extends BaseController
{
    public function __construct(
        private readonly StoreRepository $storeRepository,
        private readonly PaymentMethodRepository $paymentMethodRepository,
        private readonly TransactionRepository $transactionRepository,
        private readonly TransactionFactory $transactionFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] PayDayPayload $payload = new PayDayPayload(),
    ): Response {
        $payments = $payload->payments;
        if (!$payments instanceof PayDayPayments) {
            $paymentMethods = $this->paymentMethodRepository->findAll();
            $serializer = new Serializer([new GetSetMethodNormalizer()], ['json' => new JsonEncoder()]);

            return $this->render(
                'admin/payday.html.twig',
                [
                    'stores' => $this->storeRepository->getActive(),
                    'lastRecipeNo' => $this->transactionRepository->getLastRecipeNo() + 1,
                    'paymentMethods' => $paymentMethods,
                    'paymentMethodsString' => $serializer->serialize($paymentMethods, 'json'),
                ]
            );
        }

        foreach ($payments->date as $i => $dateCobro) {
            if (!$dateCobro) {
                continue;
            }

            $transaction = $this->createPaymentTransaction($payments, $i, $dateCobro);

            if ($transaction instanceof Transaction) {
                $this->entityManager->persist($transaction);
            }
        }

        $this->entityManager->flush();
        $this->addFlash('success', 'Sa ha pagado...');

        return $this->redirectToRoute('welcome');
    }

    private function createPaymentTransaction(PayDayPayments $payments, int $i, string $dateCobro): ?Transaction
    {
        $store = $this->storeRepository->find((int) ($payments->store[$i] ?? 0));

        if (!$store) {
            return null;
        }

        $method = $this->paymentMethodRepository->find((int) ($payments->method[$i] ?? 0));

        if (!$method) {
            throw new UnexpectedValueException('Invalid payment method.');
        }

        $user = $store->getUser();

        if (!$user) {
            throw new UnexpectedValueException('Store has no user.');
        }

        return $this->transactionFactory->createPayment(
            $store,
            $user,
            $method,
            $dateCobro,
            (int) ($payments->recipe[$i] ?? 0),
            (int) ($payments->document[$i] ?? 0),
            (int) ($payments->deposit[$i] ?? 0),
            $payments->amount[$i] ?? '',
            $payments->comment[$i] ?? '',
        );
    }
}
