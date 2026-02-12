<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use DateTime;
use App\Entity\Deposit;
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
            'date' => new DateTime(),
            'document' => self::faker()->numerify('###'),
            'amount' => '0',
        ];
    }
}
