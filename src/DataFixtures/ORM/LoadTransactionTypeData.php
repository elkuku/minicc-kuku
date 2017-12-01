<?php

namespace App\DataFixtures\ORM;

use App\Entity\TransactionType;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadTransactionTypeData
 */
class LoadTransactionTypeData implements FixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $names = ['Alquiler', 'Pago', 'Saldo Inicial', 'Ajuste'];

        foreach ($names as $name) {
            $transactionType = new TransactionType();

            $transactionType->setName($name);

            $manager->persist($transactionType);
        }

        $manager->flush();
    }
}
