<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use JsonSerializable;

/**
 * Deposit
 *
 * @ORM\Table(name="deposit")
 * @ORM\Entity(repositoryClass="App\Repository\DepositRepository")
 */
class Deposit implements JsonSerializable
{
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 * @var PaymentMethod
	 * @ManyToOne(targetEntity="PaymentMethod")
	 */
	private $entity;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="date", type="date", nullable=false)
	 */
	private $date;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", length=150, nullable=false)
	 */
	private $document;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="decimal", precision=13, scale=2, nullable=false)
	 */
	private $amount;

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set entity
	 *
	 * @param PaymentMethod $entity
	 *
	 * @return Deposit
	 */
	public function setEntity(PaymentMethod $entity)
	{
		if (1 == $entity->getId())
		{
			throw new \UnexpectedValueException(
				'The entity with ID "1" is supposed to be the BAR payment method!'
			);
		}

		$this->entity = $entity;

		return $this;
	}

	/**
	 * Get entity
	 *
	 * @return PaymentMethod
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * Set date
	 *
	 * @param \DateTime $date
	 *
	 * @return Deposit
	 */
	public function setDate(\DateTime $date)
	{
		$this->date = $date;

		return $this;
	}

	/**
	 * Get date
	 *
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * Set document
	 *
	 * @param string $document
	 *
	 * @return Deposit
	 */
	public function setDocument($document)
	{
		$this->document = $document;

		return $this;
	}

	/**
	 * Get document
	 *
	 * @return string
	 */
	public function getDocument()
	{
		return $this->document;
	}

	/**
	 * Set amount
	 *
	 * @param string $amount
	 *
	 * @return Deposit
	 */
	public function setAmount($amount)
	{
		$this->amount = $amount;

		return $this;
	}

	/**
	 * Get amount
	 *
	 * @return string
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize()
	{
		return [
			'id'       => $this->id,
			'amount'   => $this->amount,
			'document' => $this->document,
			'date'     => $this->date->format('Y-m-d'),
		];
	}
}
