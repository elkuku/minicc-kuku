<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\PaymentMethod;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<PaymentMethod>
 */
final class PaymentMethodFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return PaymentMethod::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'name' => self::faker()->word(),
        ];
    }
}
