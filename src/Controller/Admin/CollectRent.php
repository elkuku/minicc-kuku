<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Transaction;
use App\Repository\PaymentMethodRepository;
use App\Repository\StoreRepository;
use App\Repository\UserRepository;
use App\Type\TransactionType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        private readonly PaymentMethodRepository $paymentMethodRepository
    ) {}

    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $values = $request->request->all('values');
        if (!$values) {
            return $this->render(
                'admin/collect-rent.html.twig',
                [
                    'stores' => $this->storeRepository->getActive(),
                ]
            );
        }

        $users = $request->request->all('users');
        $method = $this->paymentMethodRepository->find(1);

        if (!$method) {
            throw new UnexpectedValueException('Invalid payment method.');
        }

        foreach ($values as $storeId => $value) {
            if (!$value) {
                continue;
            }

            $user = $this->userRepository->find((int)$users[$storeId]);

            if (!$user) {
                throw new UnexpectedValueException('Store has no user.');
            }

            $store = $this->storeRepository->find((int)$storeId);

            if (!$store) {
                throw new UnexpectedValueException('Store does not exist.');
            }

            $transaction = new Transaction()
                ->setDate(
                    new DateTime((string)$request->request->get('date_cobro'))
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
}
