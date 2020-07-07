<?php

namespace App\Tests\Fixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $object = new User();

        $object->setName('Test');
        $object->setEmail('test@email.com');

        $manager->persist($object);

        $manager->flush();
    }
}
