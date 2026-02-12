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

        $this->assertNull($paymentMethod->getId());
    }

    public function testNameGetterSetter(): void
    {
        $paymentMethod = new PaymentMethod();

        $this->assertNull($paymentMethod->getName());

        $result = $paymentMethod->setName('Cash');

        $this->assertSame($paymentMethod, $result);
        $this->assertSame('Cash', $paymentMethod->getName());
    }

    public function testSetNameWithDifferentValues(): void
    {
        $paymentMethod = new PaymentMethod();

        $paymentMethod->setName('Bank Transfer');
        $this->assertSame('Bank Transfer', $paymentMethod->getName());

        $paymentMethod->setName('Credit Card');
        $this->assertSame('Credit Card', $paymentMethod->getName());

        $paymentMethod->setName('Bar');
        $this->assertSame('Bar', $paymentMethod->getName());
    }

    public function testSetNameWithEmptyString(): void
    {
        $paymentMethod = new PaymentMethod();

        $paymentMethod->setName('');

        $this->assertSame('', $paymentMethod->getName());
    }

    public function testSetNameWithLongString(): void
    {
        $paymentMethod = new PaymentMethod();
        $longName = str_repeat('A', 150);

        $paymentMethod->setName($longName);

        $this->assertSame($longName, $paymentMethod->getName());
    }

    public function testSetNameWithSpecialCharacters(): void
    {
        $paymentMethod = new PaymentMethod();

        $paymentMethod->setName('Tarjeta de Crédito - Visa/MC');

        $this->assertSame('Tarjeta de Crédito - Visa/MC', $paymentMethod->getName());
    }

    public function testFluentInterface(): void
    {
        $paymentMethod = new PaymentMethod();

        $result = $paymentMethod->setName('Test');

        $this->assertInstanceOf(PaymentMethod::class, $result);
        $this->assertSame($paymentMethod, $result);
    }
}
