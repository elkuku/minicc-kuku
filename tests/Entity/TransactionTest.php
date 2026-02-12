<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Deposit;
use App\Entity\PaymentMethod;
use App\Entity\Store;
use App\Entity\Transaction;
use App\Entity\User;
use App\Type\Gender;
use App\Type\TransactionType;
use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class TransactionTest extends TestCase
{
    public function testGetIdReturnsNullForNewEntity(): void
    {
        $transaction = new Transaction();

        $this->assertNull($transaction->getId());
    }

    public function testSetId(): void
    {
        $transaction = new Transaction();

        $result = $transaction->setId(42);

        $this->assertSame($transaction, $result);
        $this->assertSame(42, $transaction->getId());
    }

    public function testDateGetterSetter(): void
    {
        $transaction = new Transaction();
        $date = new DateTime('2024-06-15');

        $result = $transaction->setDate($date);

        $this->assertSame($transaction, $result);
        $this->assertSame($date, $transaction->getDate());
    }

    public function testAmountGetterSetter(): void
    {
        $transaction = new Transaction();

        $this->assertSame('0', $transaction->getAmount());

        $result = $transaction->setAmount('1500.50');

        $this->assertSame($transaction, $result);
        $this->assertSame('1500.50', $transaction->getAmount());
    }

    public function testDocumentGetterSetter(): void
    {
        $transaction = new Transaction();

        $result = $transaction->setDocument(123456);

        $this->assertSame($transaction, $result);
        $this->assertSame(123456, $transaction->getDocument());
    }

    public function testDepIdGetterSetter(): void
    {
        $transaction = new Transaction();

        $this->assertNull($transaction->getDepId());

        $result = $transaction->setDepId(99);

        $this->assertSame($transaction, $result);
        $this->assertSame(99, $transaction->getDepId());
    }

    public function testRecipeNoGetterSetter(): void
    {
        $transaction = new Transaction();

        $this->assertSame(0, $transaction->getRecipeNo());

        $result = $transaction->setRecipeNo(12345);

        $this->assertSame($transaction, $result);
        $this->assertSame(12345, $transaction->getRecipeNo());
    }

    public function testCommentGetterSetter(): void
    {
        $transaction = new Transaction();

        $this->assertNull($transaction->getComment());

        $result = $transaction->setComment('Test comment');

        $this->assertSame($transaction, $result);
        $this->assertSame('Test comment', $transaction->getComment());
    }

    public function testCommentCanBeSetToNull(): void
    {
        $transaction = new Transaction();
        $transaction->setComment('Some comment');

        $transaction->setComment(null);

        $this->assertNull($transaction->getComment());
    }

    public function testUserGetterSetter(): void
    {
        $transaction = new Transaction();
        $user = $this->createUser();

        $result = $transaction->setUser($user);

        $this->assertSame($transaction, $result);
        $this->assertSame($user, $transaction->getUser());
    }

    public function testStoreGetterSetter(): void
    {
        $transaction = new Transaction();
        $store = new Store();
        $store->setDestination('Test Store');

        $result = $transaction->setStore($store);

        $this->assertSame($transaction, $result);
        $this->assertSame($store, $transaction->getStore());
    }

    public function testTypeGetterSetter(): void
    {
        $transaction = new Transaction();

        $result = $transaction->setType(TransactionType::rent);

        $this->assertSame($transaction, $result);
        $this->assertSame(TransactionType::rent, $transaction->getType());
    }

    public function testMethodGetterSetter(): void
    {
        $transaction = new Transaction();
        $paymentMethod = new PaymentMethod();
        $paymentMethod->setName('Bank Transfer');

        $result = $transaction->setMethod($paymentMethod);

        $this->assertSame($transaction, $result);
        $this->assertSame($paymentMethod, $transaction->getMethod());
    }

    public function testDepositGetterSetter(): void
    {
        $transaction = new Transaction();

        $this->assertNotInstanceOf(Deposit::class, $transaction->getDeposit());

        $deposit = new Deposit();
        $result = $transaction->setDeposit($deposit);

        $this->assertSame($transaction, $result);
        $this->assertSame($deposit, $transaction->getDeposit());
    }

    public function testSetDepositToNull(): void
    {
        $transaction = new Transaction();
        $deposit = new Deposit();

        $transaction->setDeposit($deposit);
        $this->assertSame($deposit, $transaction->getDeposit());

        $transaction->setDeposit(null);
        $this->assertNull($transaction->getDeposit());
    }

    public function testJsonSerialize(): void
    {
        $transaction = new Transaction();

        $user = $this->createUser();
        $this->setEntityId($user, 5);

        $store = new Store();
        $store->setDestination('Test Store');
        $this->setEntityId($store, 10);

        $paymentMethod = new PaymentMethod();
        $paymentMethod->setName('Cash');
        $this->setEntityId($paymentMethod, 1);

        $transaction->setId(42);
        $transaction->setUser($user);
        $transaction->setStore($store);
        $transaction->setType(TransactionType::payment);
        $transaction->setMethod($paymentMethod);
        $transaction->setDate(new DateTime('2024-03-15'));
        $transaction->setAmount('500.00');
        $transaction->setDocument(789);
        $transaction->setDepId(100);
        $transaction->setRecipeNo(456);

        $json = $transaction->jsonSerialize();

        $this->assertSame(42, $json['id']);
        $this->assertSame(10, $json['store']);
        $this->assertSame(5, $json['user']);
        $this->assertSame('payment', $json['type']);
        $this->assertSame(1, $json['method']);
        $this->assertSame('2024-03-15', $json['date']);
        $this->assertSame('500.00', $json['amount']);
        $this->assertSame(789, $json['document']);
        $this->assertSame(100, $json['depId']);
        $this->assertSame(456, $json['recipeNo']);
    }

    public function testJsonSerializeWithNullDepId(): void
    {
        $transaction = new Transaction();

        $user = $this->createUser();
        $store = new Store();
        $paymentMethod = new PaymentMethod();
        $paymentMethod->setName('Cash');

        $transaction->setUser($user);
        $transaction->setStore($store);
        $transaction->setType(TransactionType::rent);
        $transaction->setMethod($paymentMethod);
        $transaction->setDate(new DateTime('2024-01-01'));
        $transaction->setDocument(1);

        $json = $transaction->jsonSerialize();

        $this->assertNull($json['depId']);
    }

    public function testJsonSerializeWithNullMethodId(): void
    {
        $transaction = new Transaction();

        $user = $this->createUser();
        $store = new Store();
        $paymentMethod = new PaymentMethod();
        $paymentMethod->setName('New Method');

        $transaction->setUser($user);
        $transaction->setStore($store);
        $transaction->setType(TransactionType::rent);
        $transaction->setMethod($paymentMethod);
        $transaction->setDate(new DateTime('2024-01-01'));
        $transaction->setDocument(1);

        $json = $transaction->jsonSerialize();

        $this->assertNull($json['method']);
    }

    public function testAllTransactionTypes(): void
    {
        $transaction = new Transaction();

        foreach (TransactionType::cases() as $type) {
            $transaction->setType($type);
            $this->assertSame($type, $transaction->getType());
        }
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test User');
        $user->setGender(Gender::male);

        return $user;
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new ReflectionProperty($entity::class, 'id');
        $reflection->setValue($entity, $id);
    }
}
