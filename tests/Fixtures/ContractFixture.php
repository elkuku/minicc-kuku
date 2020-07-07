<?php

namespace App\Tests\Fixtures;

use App\Entity\Contract;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ContractFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $object = new Contract();

        $object->setText('TEST');

        $manager->persist($object);

        $manager->flush();
    }
}
