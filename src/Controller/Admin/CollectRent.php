<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Dto\CollectRentPayload;
use App\Entity\PaymentMethod;
use App\Entity\Transaction;
use App\Enum\PaymentMethodId;
use App\Repository\PaymentMethodRepository;
use App\Repository\StoreRepository;
use App\Repository\UserRepository;
use App\Service\TransactionFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UnexpectedValueException;

#[Route(path: '/admin/collect-rent', name: 'admin_collect_rent', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_ADMIN')]
class CollectRent extends BaseController
{
    public function __construct(
        private readonly StoreRepository $storeRepository,
        private readonly UserRepository $userRepository,
        private readonly PaymentMethodRepository $paymentMethodRepository,
        private readonly TransactionFactory $transactionFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function __invoke(
        #[MapRequestPayload] CollectRentPayload $payload = new CollectRentPayload(),
    ): Response
    {
        if ($payload->values === []) {
            return $this->render(
                'admin/collect-rent.html.twig',
                [
                    'stores' => $this->storeRepository->getActive(),
                ]
            );
        }

        $method = $this->paymentMethodRepository->find(PaymentMethodId::BAR->value);

        if (!$method) {
            throw new UnexpectedValueException('Invalid payment method.');
        }

        foreach ($payload->values as $storeId => $value) {
            if (!$value) {
                continue;
            }

            $this->entityManager->persist(
                $this->createRentTransaction((int) $storeId, (int) ($payload->users[$storeId] ?? 0), $method, $payload->date, $value)
            );
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'A cobrar...');

        return $this->redirectToRoute('welcome');
    }

    private function createRentTransaction(
        int $storeId,
        int $userId,
        PaymentMethod $method,
        string $date,
        string $value,
    ): Transaction
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            throw new UnexpectedValueException('Store has no user.');
        }

        $store = $this->storeRepository->find($storeId);

        if (!$store) {
            throw new UnexpectedValueException('Store does not exist.');
        }

        return $this->transactionFactory->createRent($store, $user, $method, $date, $value);
    }
}
