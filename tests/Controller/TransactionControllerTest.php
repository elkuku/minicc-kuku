<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Store;
use App\Entity\PaymentMethod;
use DateTime;
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
        $this->assertInstanceOf(User::class, $admin);
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
        $this->assertInstanceOf(User::class, $user);
        $client->loginUser($user);

        $client->request('GET', '/transactions');

        self::assertResponseStatusCodeSame(403);
    }

    public function testTransactionEditPostForm(): void
    {
        $transaction = $this->ensureTransactionExists();
        $transactionId = $transaction->getId();

        $this->client->request(
            'GET',
            '/transactions/edit/' . $transactionId . '?view=/transactions'
        );
        $this->client->submitForm('Guardar', [
            'transaction_type[amount]' => '99.99',
            'transaction_type[comment]' => 'Updated via test',
        ]);

        self::assertResponseRedirects('/transactions');
    }

    public function testTransactionEditPostFormDefaultRedirect(): void
    {
        $transaction = $this->ensureTransactionExists();
        $transactionId = $transaction->getId();

        $this->client->request('GET', '/transactions/edit/' . $transactionId);
        $this->client->submitForm('Guardar', [
            'transaction_type[amount]' => '88.88',
            'transaction_type[comment]' => 'Default redirect test',
        ]);

        self::assertResponseRedirects();
    }

    public function testTransactionDeleteWithViewRedirect(): void
    {
        $transaction = $this->ensureTransactionExists();
        $transactionId = $transaction->getId();

        $this->client->request('GET', '/transactions/delete/' . $transactionId . '?view=/transactions');

        self::assertResponseRedirects('/transactions');
    }

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
        $this->assertInstanceOf(Store::class, $store);

        /** @var UserRepository $userRepository */
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'admin@example.com']);
        $this->assertInstanceOf(User::class, $user);

        /** @var PaymentMethodRepository $pmRepository */
        $pmRepository = static::getContainer()->get(PaymentMethodRepository::class);
        $paymentMethod = $pmRepository->findOneBy([]);
        $this->assertInstanceOf(PaymentMethod::class, $paymentMethod);

        $transaction = new Transaction();
        $transaction->setStore($store);
        $transaction->setUser($user);
        $transaction->setType(TransactionType::payment);
        $transaction->setMethod($paymentMethod);
        $transaction->setDate(new DateTime());
        $transaction->setAmount('50.00');

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($transaction);
        $em->flush();

        return $transaction;
    }
}
