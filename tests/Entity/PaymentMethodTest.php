<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\PaymentMethod;
use PHPUnit\Framework\TestCase;

final class PaymentMethodTest extends TestCase
{
    public function testGetIdReturnsNullForNewEntity(): void
    {
        $paymentMethod = new PaymentMethod();

        self::assertNull($paymentMethod->getId());
    }

    public function testNameGetterSetter(): void
    {
        $paymentMethod = new PaymentMethod();

        self::assertNull($paymentMethod->getName());

        $result = $paymentMethod->setName('Cash');

        self::assertSame($paymentMethod, $result);
        self::assertSame('Cash', $paymentMethod->getName());
    }

    public function testSetNameWithDifferentValues(): void
    {
        $paymentMethod = new PaymentMethod();

        $paymentMethod->setName('Bank Transfer');
        self::assertSame('Bank Transfer', $paymentMethod->getName());

        $paymentMethod->setName('Credit Card');
        self::assertSame('Credit Card', $paymentMethod->getName());

        $paymentMethod->setName('Bar');
        self::assertSame('Bar', $paymentMethod->getName());
    }

    public function testSetNameWithEmptyString(): void
    {
        $paymentMethod = new PaymentMethod();

        $paymentMethod->setName('');

        self::assertSame('', $paymentMethod->getName());
    }

    public function testSetNameWithLongString(): void
    {
        $paymentMethod = new PaymentMethod();
        $longName = str_repeat('A', 150);

        $paymentMethod->setName($longName);

        self::assertSame($longName, $paymentMethod->getName());
    }

    public function testSetNameWithSpecialCharacters(): void
    {
        $paymentMethod = new PaymentMethod();

        $paymentMethod->setName('Tarjeta de Crédito - Visa/MC');

        self::assertSame('Tarjeta de Crédito - Visa/MC', $paymentMethod->getName());
    }

    public function testFluentInterface(): void
    {
        $paymentMethod = new PaymentMethod();

        $result = $paymentMethod->setName('Test');

        self::assertInstanceOf(PaymentMethod::class, $result);
        self::assertSame($paymentMethod, $result);
    }
}
