<?php

namespace App\Tests\Fixtures;

use App\Entity\Transaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TransactionFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $object = new Transaction();

	    $object->setDate(new \DateTime());

        $manager->persist($object);

        $manager->flush();
    }
}
