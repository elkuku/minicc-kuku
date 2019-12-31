<?php

namespace App\Tests\Fixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $object = new User();

	    $object->setName('Test');
	    $object->setUsername('test');
	    $object->setEmail('test@email.com');
	    $object->setPassword('t3st');

        $manager->persist($object);

        $manager->flush();
    }
}
