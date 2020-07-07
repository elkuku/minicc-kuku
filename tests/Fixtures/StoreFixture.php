<?php

namespace App\Tests\Fixtures;

use App\Entity\Store;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StoreFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $object = new Store();

        $manager->persist($object);

        $manager->flush();
    }
}
