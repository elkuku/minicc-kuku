<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Deposit;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Deposit>
 */
final class DepositFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Deposit::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'date' => new DateTimeImmutable(),
            'document' => self::faker()->numerify('###'),
            'amount' => '0',
            'entity' => PaymentMethodFactory::new(),
        ];
    }
}
