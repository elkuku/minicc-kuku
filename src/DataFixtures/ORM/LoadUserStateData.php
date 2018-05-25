<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\DataFixtures\ORM;

use App\Entity\UserState;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadTransactionTypeData
 */
class LoadUserStateData implements FixtureInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function load(ObjectManager $manager)
	{
		$names = ['Activo', 'Inactivo'];

		foreach ($names as $name)
		{
			$userState = new UserState;

			$userState->setName($name);

			$manager->persist($userState);
		}

		$manager->flush();
	}
}
