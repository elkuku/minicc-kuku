<?php

namespace App\DataFixtures;

use _HumbugBox5d215ba2066e\Nette\Utils\DateTime;
use App\Entity\Store;
use App\Entity\Transaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $store = (new Store())
            ->setValAlq(123);
        $manager->persist($store);

        $transaction = (new Transaction())
            ->setDate(new DateTime());
        $manager->persist($transaction);

        $manager->flush();
    }
}
