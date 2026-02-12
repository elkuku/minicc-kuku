<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Contract;
use App\Type\Gender;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Contract>
 */
final class ContractFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Contract::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'gender' => Gender::other,
            'text' => '',
        ];
    }
}
