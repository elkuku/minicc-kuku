<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\PaymentMethod;
use App\Entity\Store;
use App\Entity\Transaction;
use App\Entity\User;
use App\Service\TransactionFactory;
use App\Type\TransactionType;
use PHPUnit\Framework\TestCase;

final class TransactionFactoryTest extends TestCase
{
    private TransactionFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new TransactionFactory();
    }

    public function testCreatePaymentReturnsTransaction(): void
    {
        $store = $this->createStub(Store::class);
        $user = $this->createStub(User::class);
        $method = $this->createStub(PaymentMethod::class);

        $transaction = $this->factory->createPayment(
            $store,
            $user,
            $method,
            '2024-06-15',
            101,
            202,
            303,
            '150.00',
            'Test payment',
        );

        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertSame(TransactionType::payment, $transaction->getType());
        self::assertSame($store, $transaction->getStore());
        self::assertSame($user, $transaction->getUser());
        self::assertSame($method, $transaction->getMethod());
        self::assertSame(101, $transaction->getRecipeNo());
        self::assertSame(202, $transaction->getDocument());
        self::assertSame(303, $transaction->getDepId());
        self::assertSame('150.00', $transaction->getAmount());
        self::assertSame('Test payment', $transaction->getComment());
        self::assertSame('2024-06-15', $transaction->getDate()->format('Y-m-d'));
    }

    public function testCreateRentReturnsTransaction(): void
    {
        $store = $this->createStub(Store::class);
        $user = $this->createStub(User::class);
        $method = $this->createStub(PaymentMethod::class);

        $transaction = $this->factory->createRent(
            $store,
            $user,
            $method,
            '2024-06-15',
            '200.00',
        );

        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertSame(TransactionType::rent, $transaction->getType());
        self::assertSame($store, $transaction->getStore());
        self::assertSame($user, $transaction->getUser());
        self::assertSame($method, $transaction->getMethod());
        self::assertSame('2024-06-15', $transaction->getDate()->format('Y-m-d'));
    }

    public function testCreateRentSetsNegativeAmount(): void
    {
        $transaction = $this->factory->createRent(
            $this->createStub(Store::class),
            $this->createStub(User::class),
            $this->createStub(PaymentMethod::class),
            '2024-06-15',
            '250.50',
        );

        self::assertSame('-250.5', $transaction->getAmount());
    }

    public function testCreatePaymentAndRentReturnDistinctInstances(): void
    {
        $store = $this->createStub(Store::class);
        $user = $this->createStub(User::class);
        $method = $this->createStub(PaymentMethod::class);

        $a = $this->factory->createPayment($store, $user, $method, '2024-01-01', 1, 1, 1, '10.00', '');
        $b = $this->factory->createPayment($store, $user, $method, '2024-01-01', 2, 2, 2, '20.00', '');

        self::assertNotSame($a, $b);
    }
}
