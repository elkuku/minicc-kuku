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
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadPaymentMethodData
 */
class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * {@inheritdoc}
	 */
	public function setContainer(ContainerInterface $container = null): void
	{
		$this->container = $container;
	}

	/**
	 * {@inheritdoc}
	 */
	public function load(ObjectManager $manager): void
	{
		$user = new User;

		$user->setName('admin')
			->setEmail('admin@a.b')
			->setPlainPassword('test')
			->setRole('ROLE_ADMIN')
			->setPassword(
				$this->container->get('security.password_encoder')
					->encodePassword($user, $user->getPlainPassword())
			);

		$manager->persist($user);
		$manager->flush();
	}
}
