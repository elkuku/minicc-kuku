<?php

namespace App\DataFixtures;

use App\Entity\Contract;
use App\Entity\Deposit;
use App\Entity\PaymentMethod;
use App\Entity\Store;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Entity\User;
use App\Entity\UserGender;
use App\Entity\UserState;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $userStateActive = (new UserState)
            ->setName('Activo');
        $manager->persist($userStateActive);

        $userStateInactive = (new UserState)
            ->setName('Inactivo');
        $manager->persist($userStateInactive);

        $userGenderSr = (new UserGender)
            ->setName('Sr');
        $manager->persist($userGenderSr);

        $userGenderSra = (new UserGender)
            ->setName('Sra');
        $manager->persist($userGenderSra);

        $admin = (new User)
            ->setState($userStateActive)
            ->setGender($userGenderSra)
            ->setName('admin')
            ->setEmail('admin@example.com')
            ->setRole('ROLE_ADMIN');
        $manager->persist($admin);

        $user1 = (new User)
            ->setState($userStateActive)
            ->setGender($userGenderSra)
            ->setName('user1')
            ->setEmail('user1@example.com')
            ->setRole('ROLE_USER');
        $manager->persist($user1);

        $user2 = (new User)
            ->setState($userStateActive)
            ->setGender($userGenderSra)
            ->setName('user2')
            ->setEmail('user2@example.com')
            ->setRole('ROLE_USER');
        $manager->persist($user2);

        $user3 = (new User)
            ->setState($userStateInactive)
            ->setGender($userGenderSr)
            ->setName('user3')
            ->setEmail('user3@example.com')
            ->setRole('ROLE_USER');
        $manager->persist($user3);

        /*
         * Store
         */
        $store = (new Store())
            ->setValAlq(123)
            ->setUser($user1)
            ->setDestination('TEST');
        $manager->persist($store);

        /*
         * Payment methods
         */
        $names = ['Bar', 'pch-765', 'gye-1005345'];

        foreach ($names as $name) {
            $paymentMethod = (new PaymentMethod)
                ->setName($name);
            $manager->persist($paymentMethod);
        }

        /*
         * Transactions
         */
        $names = ['Alquiler', 'Pago', 'Saldo Inicial', 'Ajuste'];

        foreach ($names as $name) {
            $transactionType = (new TransactionType)
                ->setName($name);
            $manager->persist($transactionType);
        }

        $transaction = (new Transaction())
            ->setStore($store)
            ->setUser($user1)
            ->setDate(new DateTime())
            ->setType($transactionType)
            ->setMethod($paymentMethod)
            ->setAmount(123.45);
        $manager->persist($transaction);

        /*
         * Contract
         */
        $text = file_get_contents(__DIR__.'/contract-template.html');
        if ($text) {
            $contract = (new Contract)
                ->setText($text);
            $manager->persist($contract);
        }

        /*
         * Deposit
         */
        $deposit = (new Deposit())
            ->setDate(new DateTime())
            ->setDocument('123')
            ->setAmount(123);
        $manager->persist($deposit);

        $manager->flush();
    }
}
