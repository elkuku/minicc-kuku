<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 22.03.17
 * Time: 00:09
 */

namespace App\DataFixtures\ORM;

use App\Entity\PaymentMethod;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadPaymentMethodData
 */
class LoadPaymentMethodData implements FixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $names = ['Bar', 'pch-765', 'gye-1005345'];

        foreach ($names as $name) {
            $paymentMethod = new PaymentMethod();

            $paymentMethod->setName($name);

            $manager->persist($paymentMethod);
        }

        $manager->flush();
    }
}
