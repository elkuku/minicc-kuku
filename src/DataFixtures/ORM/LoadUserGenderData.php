<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\DataFixtures\ORM;

use App\Entity\UserGender;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadTransactionTypeData
 */
class LoadUserGenderData implements FixtureInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function load(ObjectManager $manager)
	{
		$names = ['Sr', 'Sra'];

		foreach ($names as $name)
		{
			$userGender = new UserGender;

			$userGender->setName($name);

			$manager->persist($userGender);
		}

		$manager->flush();
	}
}
