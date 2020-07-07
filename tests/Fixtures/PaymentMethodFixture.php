<?php

namespace App\Tests\Fixtures;

use App\Entity\PaymentMethod;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PaymentMethodFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $object = new PaymentMethod();

        $object->setName('TEST');

        $manager->persist($object);

        $manager->flush();
    }
}
