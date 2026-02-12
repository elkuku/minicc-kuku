<?php

declare(strict_types=1);

namespace App\Tests\Story;

use App\Entity\User;
use App\Type\Gender;
use App\Type\TransactionType;
use App\Tests\Factory\ContractFactory;
use App\Tests\Factory\DepositFactory;
use App\Tests\Factory\PaymentMethodFactory;
use App\Tests\Factory\StoreFactory;
use App\Tests\Factory\TransactionFactory;
use App\Tests\Factory\UserFactory;
use DateTime;
use Zenstruck\Foundry\Story;

final class AppFixtureStory extends Story
{
    public function build(): void
    {
        UserFactory::createOne([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'role' => User::ROLES['admin'],
            'gender' => Gender::female,
            'isActive' => true,
        ]);

        $user1 = UserFactory::createOne([
            'name' => 'user1',
            'email' => 'user1@example.com',
            'role' => User::ROLES['user'],
            'gender' => Gender::female,
            'isActive' => true,
        ]);

        UserFactory::createOne([
            'name' => 'user2',
            'email' => 'user2@example.com',
            'role' => User::ROLES['user'],
            'gender' => Gender::female,
            'isActive' => true,
        ]);

        UserFactory::createOne([
            'name' => 'user3',
            'email' => 'user3@example.com',
            'role' => User::ROLES['user'],
            'gender' => Gender::male,
            'isActive' => false,
        ]);

        $store = StoreFactory::createOne([
            'valAlq' => 123.0,
            'user' => $user1,
            'destination' => 'TEST',
        ]);

        PaymentMethodFactory::createOne(['name' => 'Bar']);
        $pch = PaymentMethodFactory::createOne(['name' => 'pch-765']);
        $gye = PaymentMethodFactory::createOne(['name' => 'gye-1005345']);

        TransactionFactory::createOne([
            'store' => $store,
            'user' => $user1,
            'date' => new DateTime(),
            'type' => TransactionType::payment,
            'method' => $gye,
            'amount' => '123.45',
        ]);

        $text = file_get_contents(
            dirname(__DIR__, 2).'/src/Story/contract-template.html'
        );

        if ($text) {
            ContractFactory::createOne([
                'storeNumber' => 1,
                'inqNombreapellido' => 'Tester',
                'gender' => Gender::other,
                'destination' => 'Testing',
                'valAlq' => 123.45,
                'valGarantia' => 123.45,
                'text' => $text,
            ]);
        }

        DepositFactory::createOne([
            'date' => new DateTime(),
            'document' => '123',
            'amount' => '123',
            'entity' => $pch,
        ]);
    }
}
