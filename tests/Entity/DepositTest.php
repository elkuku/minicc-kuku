<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Deposit;
use App\Entity\PaymentMethod;
use App\Entity\Transaction;
use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use UnexpectedValueException;

final class DepositTest extends TestCase
{
    public function testSetEntityThrowsExceptionForIdOne(): void
    {
        $deposit = new Deposit();
        $paymentMethod = $this->createPaymentMethodWithId(1);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The entity with ID "1" is supposed to be the BAR payment method!');

        $deposit->setEntity($paymentMethod);
    }

    public function testSetEntityAcceptsOtherIds(): void
    {
        $deposit = new Deposit();
        $paymentMethod = $this->createPaymentMethodWithId(2);

        $result = $deposit->setEntity($paymentMethod);

        self::assertSame($deposit, $result);
        self::assertSame($paymentMethod, $deposit->getEntity());
    }

    public function testSetEntityAcceptsIdThree(): void
    {
        $deposit = new Deposit();
        $paymentMethod = $this->createPaymentMethodWithId(3);

        $result = $deposit->setEntity($paymentMethod);

        self::assertSame($deposit, $result);
        self::assertSame($paymentMethod, $deposit->getEntity());
    }

    public function testJsonSerialize(): void
    {
        $deposit = new Deposit();
        $paymentMethod = $this->createPaymentMethodWithId(2);

        $deposit->setEntity($paymentMethod);
        $deposit->setDate(new DateTime('2024-03-15'));
        $deposit->setDocument('DOC-123');
        $deposit->setAmount('1500.50');

        $json = $deposit->jsonSerialize();

        self::assertArrayHasKey('id', $json);
        self::assertArrayHasKey('amount', $json);
        self::assertArrayHasKey('document', $json);
        self::assertArrayHasKey('date', $json);
        self::assertArrayHasKey('entity', $json);

        self::assertSame('1500.50', $json['amount']);
        self::assertSame('DOC-123', $json['document']);
        self::assertSame('2024-03-15', $json['date']);
        self::assertSame(2, $json['entity']);
    }

    public function testJsonSerializeWithNullId(): void
    {
        $deposit = new Deposit();
        $paymentMethod = $this->createPaymentMethodWithId(2);

        $deposit->setEntity($paymentMethod);
        $deposit->setDate(new DateTime('2024-01-01'));
        $deposit->setDocument('TEST');
        $deposit->setAmount('100.00');

        $json = $deposit->jsonSerialize();

        self::assertNull($json['id']);
    }

    public function testDateGetterSetter(): void
    {
        $deposit = new Deposit();
        $date = new DateTime('2024-06-15');

        $result = $deposit->setDate($date);

        self::assertSame($deposit, $result);
        self::assertSame($date, $deposit->getDate());
    }

    public function testDocumentGetterSetter(): void
    {
        $deposit = new Deposit();

        $result = $deposit->setDocument('INVOICE-2024-001');

        self::assertSame($deposit, $result);
        self::assertSame('INVOICE-2024-001', $deposit->getDocument());
    }

    public function testAmountGetterSetter(): void
    {
        $deposit = new Deposit();

        $result = $deposit->setAmount('9999.99');

        self::assertSame($deposit, $result);
        self::assertSame('9999.99', $deposit->getAmount());
    }

    public function testGetIdReturnsNullForNewEntity(): void
    {
        $deposit = new Deposit();

        self::assertNull($deposit->getId());
    }

    public function testGetTransactionReturnsNullByDefault(): void
    {
        $deposit = new Deposit();

        self::assertNull($deposit->getTransaction());
    }

    public function testSetTransactionSetsOwningSide(): void
    {
        $deposit = new Deposit();
        $transaction = new Transaction();

        $result = $deposit->setTransaction($transaction);

        self::assertSame($deposit, $result);
        self::assertSame($transaction, $deposit->getTransaction());
        self::assertSame($deposit, $transaction->getDeposit());
    }

    public function testSetTransactionToNullUnsetsOwningSide(): void
    {
        $deposit = new Deposit();
        $transaction = new Transaction();

        $deposit->setTransaction($transaction);
        self::assertSame($transaction, $deposit->getTransaction());

        $deposit->setTransaction(null);

        self::assertNull($deposit->getTransaction());
        self::assertNull($transaction->getDeposit());
    }

    private function createPaymentMethodWithId(int $id): PaymentMethod
    {
        $paymentMethod = new PaymentMethod();
        $paymentMethod->setName('Test Payment ' . $id);

        $reflection = new ReflectionProperty(PaymentMethod::class, 'id');
        $reflection->setValue($paymentMethod, $id);

        return $paymentMethod;
    }
}
