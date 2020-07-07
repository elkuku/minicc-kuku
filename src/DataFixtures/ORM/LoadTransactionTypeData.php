<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\DataFixtures\ORM;

use App\Entity\TransactionType;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class LoadTransactionTypeData
 */
class LoadTransactionTypeData implements FixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        $names = ['Alquiler', 'Pago', 'Saldo Inicial', 'Ajuste'];

        foreach ($names as $name) {
            $transactionType = new TransactionType;

            $transactionType->setName($name);

            $manager->persist($transactionType);
        }

        $manager->flush();
    }
}
