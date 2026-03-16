<?php

declare(strict_types=1);

namespace App\Tests\Enum;

use App\Enum\PaymentMethodId;
use PHPUnit\Framework\TestCase;

final class PaymentMethodIdTest extends TestCase
{
    public function testBarHasValueOne(): void
    {
        self::assertSame(1, PaymentMethodId::BAR->value);
    }

    public function testBankHasValueTwo(): void
    {
        self::assertSame(2, PaymentMethodId::BANK->value);
    }

    public function testFromValueReturnsCorrectCase(): void
    {
        self::assertSame(PaymentMethodId::BAR, PaymentMethodId::from(1));
        self::assertSame(PaymentMethodId::BANK, PaymentMethodId::from(2));
    }

    public function testCasesAreExhaustive(): void
    {
        self::assertCount(2, PaymentMethodId::cases());
    }
}
