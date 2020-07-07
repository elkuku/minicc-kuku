<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 22.03.17
 * Time: 00:09
 */

namespace App\DataFixtures\ORM;

use App\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadUserData implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $user = new User;

        $user->setName('admin')
            ->setEmail('admin@a.b')
            ->setRole('ROLE_ADMIN');

        $manager->persist($user);
        $manager->flush();
    }
}
