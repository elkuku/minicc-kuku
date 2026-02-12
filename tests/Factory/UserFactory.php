<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\User;
use App\Type\Gender;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return User::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'email' => self::faker()->unique()->safeEmail(),
            'name' => self::faker()->userName(),
            'role' => User::ROLES['user'],
            'gender' => Gender::female,
            'isActive' => true,
            'inqCi' => '',
        ];
    }
}
