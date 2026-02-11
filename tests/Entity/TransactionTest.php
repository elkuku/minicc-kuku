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

        self::assertNull($transaction->getId());
    }

    public function testSetId(): void
    {
        $transaction = new Transaction();

        $result = $transaction->setId(42);

        self::assertSame($transaction, $result);
        self::assertSame(42, $transaction->getId());
    }

    public function testDateGetterSetter(): void
    {
        $transaction = new Transaction();
        $date = new DateTime('2024-06-15');

        $result = $transaction->setDate($date);

        self::assertSame($transaction, $result);
        self::assertSame($date, $transaction->getDate());
    }

    public function testAmountGetterSetter(): void
    {
        $transaction = new Transaction();

        self::assertSame('0', $transaction->getAmount());

        $result = $transaction->setAmount('1500.50');

        self::assertSame($transaction, $result);
        self::assertSame('1500.50', $transaction->getAmount());
    }

    public function testDocumentGetterSetter(): void
    {
        $transaction = new Transaction();

        $result = $transaction->setDocument(123456);

        self::assertSame($transaction, $result);
        self::assertSame(123456, $transaction->getDocument());
    }

    public function testDepIdGetterSetter(): void
    {
        $transaction = new Transaction();

        self::assertNull($transaction->getDepId());

        $result = $transaction->setDepId(99);

        self::assertSame($transaction, $result);
        self::assertSame(99, $transaction->getDepId());
    }

    public function testRecipeNoGetterSetter(): void
    {
        $transaction = new Transaction();

        self::assertSame(0, $transaction->getRecipeNo());

        $result = $transaction->setRecipeNo(12345);

        self::assertSame($transaction, $result);
        self::assertSame(12345, $transaction->getRecipeNo());
    }

    public function testCommentGetterSetter(): void
    {
        $transaction = new Transaction();

        self::assertNull($transaction->getComment());

        $result = $transaction->setComment('Test comment');

        self::assertSame($transaction, $result);
        self::assertSame('Test comment', $transaction->getComment());
    }

    public function testCommentCanBeSetToNull(): void
    {
        $transaction = new Transaction();
        $transaction->setComment('Some comment');

        $transaction->setComment(null);

        self::assertNull($transaction->getComment());
    }

    public function testUserGetterSetter(): void
    {
        $transaction = new Transaction();
        $user = $this->createUser();

        $result = $transaction->setUser($user);

        self::assertSame($transaction, $result);
        self::assertSame($user, $transaction->getUser());
    }

    public function testStoreGetterSetter(): void
    {
        $transaction = new Transaction();
        $store = new Store();
        $store->setDestination('Test Store');

        $result = $transaction->setStore($store);

        self::assertSame($transaction, $result);
        self::assertSame($store, $transaction->getStore());
    }

    public function testTypeGetterSetter(): void
    {
        $transaction = new Transaction();

        $result = $transaction->setType(TransactionType::rent);

        self::assertSame($transaction, $result);
        self::assertSame(TransactionType::rent, $transaction->getType());
    }

    public function testMethodGetterSetter(): void
    {
        $transaction = new Transaction();
        $paymentMethod = new PaymentMethod();
        $paymentMethod->setName('Bank Transfer');

        $result = $transaction->setMethod($paymentMethod);

        self::assertSame($transaction, $result);
        self::assertSame($paymentMethod, $transaction->getMethod());
    }

    public function testDepositGetterSetter(): void
    {
        $transaction = new Transaction();

        self::assertNull($transaction->getDeposit());

        $deposit = new Deposit();
        $result = $transaction->setDeposit($deposit);

        self::assertSame($transaction, $result);
        self::assertSame($deposit, $transaction->getDeposit());
    }

    public function testSetDepositToNull(): void
    {
        $transaction = new Transaction();
        $deposit = new Deposit();

        $transaction->setDeposit($deposit);
        self::assertSame($deposit, $transaction->getDeposit());

        $transaction->setDeposit(null);
        self::assertNull($transaction->getDeposit());
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

        self::assertSame(42, $json['id']);
        self::assertSame(10, $json['store']);
        self::assertSame(5, $json['user']);
        self::assertSame('payment', $json['type']);
        self::assertSame(1, $json['method']);
        self::assertSame('2024-03-15', $json['date']);
        self::assertSame('500.00', $json['amount']);
        self::assertSame(789, $json['document']);
        self::assertSame(100, $json['depId']);
        self::assertSame(456, $json['recipeNo']);
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

        self::assertNull($json['depId']);
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

        self::assertNull($json['method']);
    }

    public function testAllTransactionTypes(): void
    {
        $transaction = new Transaction();

        foreach (TransactionType::cases() as $type) {
            $transaction->setType($type);
            self::assertSame($type, $transaction->getType());
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
