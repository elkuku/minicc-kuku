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

        $this->assertSame($deposit, $result);
        $this->assertSame($paymentMethod, $deposit->getEntity());
    }

    public function testSetEntityAcceptsIdThree(): void
    {
        $deposit = new Deposit();
        $paymentMethod = $this->createPaymentMethodWithId(3);

        $result = $deposit->setEntity($paymentMethod);

        $this->assertSame($deposit, $result);
        $this->assertSame($paymentMethod, $deposit->getEntity());
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

        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('amount', $json);
        $this->assertArrayHasKey('document', $json);
        $this->assertArrayHasKey('date', $json);
        $this->assertArrayHasKey('entity', $json);

        $this->assertSame('1500.50', $json['amount']);
        $this->assertSame('DOC-123', $json['document']);
        $this->assertSame('2024-03-15', $json['date']);
        $this->assertSame(2, $json['entity']);
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

        $this->assertNull($json['id']);
    }

    public function testDateGetterSetter(): void
    {
        $deposit = new Deposit();
        $date = new DateTime('2024-06-15');

        $result = $deposit->setDate($date);

        $this->assertSame($deposit, $result);
        $this->assertSame($date, $deposit->getDate());
    }

    public function testDocumentGetterSetter(): void
    {
        $deposit = new Deposit();

        $result = $deposit->setDocument('INVOICE-2024-001');

        $this->assertSame($deposit, $result);
        $this->assertSame('INVOICE-2024-001', $deposit->getDocument());
    }

    public function testAmountGetterSetter(): void
    {
        $deposit = new Deposit();

        $result = $deposit->setAmount('9999.99');

        $this->assertSame($deposit, $result);
        $this->assertSame('9999.99', $deposit->getAmount());
    }

    public function testGetIdReturnsNullForNewEntity(): void
    {
        $deposit = new Deposit();

        $this->assertNull($deposit->getId());
    }

    public function testGetTransactionReturnsNullByDefault(): void
    {
        $deposit = new Deposit();

        $this->assertNotInstanceOf(Transaction::class, $deposit->getTransaction());
    }

    public function testSetTransactionSetsOwningSide(): void
    {
        $deposit = new Deposit();
        $transaction = new Transaction();

        $result = $deposit->setTransaction($transaction);

        $this->assertSame($deposit, $result);
        $this->assertSame($transaction, $deposit->getTransaction());
        $this->assertSame($deposit, $transaction->getDeposit());
    }

    public function testSetTransactionToNullUnsetsOwningSide(): void
    {
        $deposit = new Deposit();
        $transaction = new Transaction();

        $deposit->setTransaction($transaction);
        $this->assertSame($transaction, $deposit->getTransaction());

        $deposit->setTransaction(null);

        $this->assertNull($deposit->getTransaction());
        $this->assertNotInstanceOf(Deposit::class, $transaction->getDeposit());
    }

    public function testSetTransactionReplacesExistingTransaction(): void
    {
        $deposit = new Deposit();
        $transaction1 = new Transaction();
        $transaction2 = new Transaction();

        $deposit->setTransaction($transaction1);
        $this->assertSame($transaction1, $deposit->getTransaction());
        $this->assertSame($deposit, $transaction1->getDeposit());

        $deposit->setTransaction($transaction2);
        $this->assertSame($transaction2, $deposit->getTransaction());
        $this->assertSame($deposit, $transaction2->getDeposit());
    }

    public function testSetTransactionToNullWhenAlreadyNull(): void
    {
        $deposit = new Deposit();

        $result = $deposit->setTransaction(null);

        $this->assertSame($deposit, $result);
        $this->assertNotInstanceOf(Transaction::class, $deposit->getTransaction());
    }

    public function testSetTransactionSkipsSetDepositWhenAlreadySet(): void
    {
        $deposit = new Deposit();
        $transaction = new Transaction();

        // Manually set the owning side first
        $transaction->setDeposit($deposit);

        // Now set via the inverse side - should skip setDeposit since it's already set
        $deposit->setTransaction($transaction);

        $this->assertSame($transaction, $deposit->getTransaction());
        $this->assertSame($deposit, $transaction->getDeposit());
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
