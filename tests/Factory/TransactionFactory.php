<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use DateTime;
use App\Entity\Transaction;
use App\Type\TransactionType;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Transaction>
 */
final class TransactionFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Transaction::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'store' => StoreFactory::new(),
            'user' => UserFactory::new(),
            'date' => new DateTime(),
            'type' => TransactionType::payment,
            'method' => PaymentMethodFactory::new(),
            'amount' => '0',
        ];
    }
}
