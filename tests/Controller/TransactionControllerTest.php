<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Transaction;
use App\Repository\PaymentMethodRepository;
use App\Repository\StoreRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use App\Type\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TransactionControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $admin = $userRepository->findOneBy(['email' => 'admin@example.com']);
        self::assertNotNull($admin);
        $this->client->loginUser($admin);
    }

    public function testTransactionIndex(): void
    {
        $this->client->request('GET', '/transactions');

        self::assertResponseIsSuccessful();
        self::assertRouteSame('transactions_index');
    }

    public function testTransactionEditGetForm(): void
    {
        $transaction = $this->ensureTransactionExists();
        $transactionId = $transaction->getId();

        $this->client->request('GET', '/transactions/edit/' . $transactionId);

        self::assertResponseIsSuccessful();
        self::assertRouteSame('transactions_edit');
    }

    public function testTransactionDeniedForRegularUser(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'user1@example.com']);
        self::assertNotNull($user);
        $client->loginUser($user);

        $client->request('GET', '/transactions');

        self::assertResponseStatusCodeSame(403);
    }

    // Delete test last since it modifies the database
    public function testTransactionDelete(): void
    {
        $transaction = $this->ensureTransactionExists();
        $transactionId = $transaction->getId();

        $this->client->request('GET', '/transactions/delete/' . $transactionId);

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertRouteSame('transactions_index');
    }

    private function ensureTransactionExists(): Transaction
    {
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = static::getContainer()->get(TransactionRepository::class);
        $transaction = $transactionRepository->findOneBy([]);

        if ($transaction) {
            return $transaction;
        }

        /** @var StoreRepository $storeRepository */
        $storeRepository = static::getContainer()->get(StoreRepository::class);
        $store = $storeRepository->findOneBy([]);
        self::assertNotNull($store);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'admin@example.com']);
        self::assertNotNull($user);

        /** @var PaymentMethodRepository $pmRepository */
        $pmRepository = static::getContainer()->get(PaymentMethodRepository::class);
        $paymentMethod = $pmRepository->findOneBy([]);
        self::assertNotNull($paymentMethod);

        $transaction = new Transaction();
        $transaction->setStore($store);
        $transaction->setUser($user);
        $transaction->setType(TransactionType::payment);
        $transaction->setMethod($paymentMethod);
        $transaction->setDate(new \DateTime());
        $transaction->setAmount('50.00');

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($transaction);
        $em->flush();

        return $transaction;
    }
}
