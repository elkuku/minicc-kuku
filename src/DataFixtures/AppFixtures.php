<?php

namespace App\DataFixtures;

use App\Entity\Contract;
use App\Entity\Deposit;
use App\Entity\PaymentMethod;
use App\Entity\Store;
use App\Entity\Transaction;
use App\Entity\User;
use App\Type\Gender;
use App\Type\TransactionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $admin = (new User())
            ->setIsActive(true)
            ->setGender(Gender::female)
            ->setName('admin')
            ->setEmail('admin@example.com')
            ->setRole(User::ROLES['admin']);
        $manager->persist($admin);

        $user1 = (new User())
            ->setIsActive(true)
            ->setGender(Gender::female)
            ->setName('user1')
            ->setEmail('user1@example.com')
            ->setRole(User::ROLES['user']);
        $manager->persist($user1);

        $user2 = (new User())
            ->setIsActive(true)
            ->setGender(Gender::female)
            ->setName('user2')
            ->setEmail('user2@example.com')
            ->setRole(User::ROLES['user']);
        $manager->persist($user2);

        $user3 = (new User())
            ->setIsActive(false)
            ->setGender(Gender::male)
            ->setName('user3')
            ->setEmail('user3@example.com')
            ->setRole(User::ROLES['user']);
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
            $paymentMethod = (new PaymentMethod())
                ->setName($name);
            $manager->persist($paymentMethod);
        }

        $transaction = (new Transaction())
            ->setStore($store)
            ->setUser($user1)
            ->setDate(new \DateTime())
            ->setType(TransactionType::payment)
            ->setMethod($paymentMethod)
            ->setAmount('123.45');
        $manager->persist($transaction);

        /*
         * Contract
         */
        $text = file_get_contents(__DIR__ . '/contract-template.html');
        if ($text) {
            $contract = (new Contract())
                ->setStoreNumber(1)
                ->setInqNombreapellido('Tester')
                ->setGender(Gender::other)
                ->setDestination('Testing')
                ->setValAlq(123.45)
                ->setValGarantia(123.45)
                ->setText($text);
            $manager->persist($contract);
        }

        /*
         * Deposit
         */
        $deposit = (new Deposit())
            ->setDate(new \DateTime())
            ->setDocument('123')
            ->setAmount(123);
        $manager->persist($deposit);

        $manager->flush();
    }
}
