<?php

declare(strict_types=1);

namespace App\Tests\Dto;

use App\Dto\DepositDto;
use App\Entity\Deposit;
use App\Entity\PaymentMethod;
use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class DepositDtoTest extends TestCase
{
    public function testFromDepositMapsAllFields(): void
    {
        $paymentMethod = $this->createPaymentMethodWithId(2);
        $deposit = new Deposit();
        $deposit->setEntity($paymentMethod);
        $deposit->setDate(new DateTime('2024-03-15'));
        $deposit->setDocument('DOC-123');
        $deposit->setAmount('1500.50');

        $dto = DepositDto::fromDeposit($deposit);

        self::assertNull($dto->id);
        self::assertSame('1500.50', $dto->amount);
        self::assertSame('DOC-123', $dto->document);
        self::assertSame('2024-03-15', $dto->date);
        self::assertSame(2, $dto->entity);
    }

    public function testJsonSerializeReturnsExpectedKeys(): void
    {
        $dto = new DepositDto(id: 42, amount: '100.00', document: 'INV-1', date: '2024-01-01', entity: 2);

        $json = $dto->jsonSerialize();

        self::assertSame(['id', 'amount', 'document', 'date', 'entity'], array_keys($json));
    }

    public function testJsonSerializeValues(): void
    {
        $dto = new DepositDto(id: 7, amount: '250.75', document: 'REF-99', date: '2024-06-30', entity: 3);

        $json = $dto->jsonSerialize();

        self::assertSame(7, $json['id']);
        self::assertSame('250.75', $json['amount']);
        self::assertSame('REF-99', $json['document']);
        self::assertSame('2024-06-30', $json['date']);
        self::assertSame(3, $json['entity']);
    }

    public function testJsonSerializeWithNullId(): void
    {
        $dto = new DepositDto(id: null, amount: '0.00', document: 'X', date: '2024-01-01', entity: 2);

        self::assertNull($dto->jsonSerialize()['id']);
    }

    public function testDoesNotExposeExtraFields(): void
    {
        $paymentMethod = $this->createPaymentMethodWithId(2);
        $deposit = new Deposit();
        $deposit->setEntity($paymentMethod);
        $deposit->setDate(new DateTime('2024-01-01'));
        $deposit->setDocument('DOC');
        $deposit->setAmount('10.00');

        $dto = DepositDto::fromDeposit($deposit);
        $json = $dto->jsonSerialize();

        self::assertArrayNotHasKey('transaction', $json);
        self::assertCount(5, $json);
    }

    private function createPaymentMethodWithId(int $id): PaymentMethod
    {
        $paymentMethod = new PaymentMethod();
        $paymentMethod->setName('Test');

        $reflection = new ReflectionProperty(PaymentMethod::class, 'id');
        $reflection->setValue($paymentMethod, $id);

        return $paymentMethod;
    }
}
