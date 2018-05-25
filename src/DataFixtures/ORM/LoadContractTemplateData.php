<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 22.03.17
 * Time: 00:09
 */

namespace App\DataFixtures\ORM;

use App\Entity\Contract;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadContractTemplateData
 */
class LoadContractTemplateData implements FixtureInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function load(ObjectManager $manager)
	{
		$contract = new Contract();

		$contract->setText(file_get_contents(__DIR__ . '/../contract-template.html'));

		$manager->persist($contract);
		$manager->flush();
	}
}
