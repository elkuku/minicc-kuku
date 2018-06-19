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
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 */
class Transaction implements JsonSerializable
{
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	protected $id;

	/**
	 * @var Store
	 *
	 * @ORM\ManyToOne(targetEntity="Store")
	 */
	protected $store;

	/**
	 * @var User
	 *
	 * @ORM\ManyToOne(targetEntity="User")
	 */
	protected $user;

	/**
	 * The type
	 * Alquiler, Pago, etc.
	 *
	 * @var TransactionType
	 * @ManyToOne(targetEntity="TransactionType")
	 */
	private $type;

	/**
	 * The method
	 * Bar, bank, etc.
	 *
	 * @var PaymentMethod
	 * @ManyToOne(targetEntity="PaymentMethod")
	 */
	private $method;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="date", nullable=false)
	 */
	private $date = '0000-00-00';

	/**
	 * @var float
	 *
	 * @ORM\Column(type="decimal", precision=13, scale=2, nullable=false)
	 */
	private $amount = '0.00';

	/**
	 * @ORM\Column(type="integer", length=20, nullable=true)
	 */
	private $document;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $depId;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $recipeNo;

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
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @param \DateTime $date
	 *
	 * @return $this
	 */
	public function setDate(\DateTime $date)
	{
		$this->date = $date;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * @param float $amount
	 *
	 * @return $this
	 */
	public function setAmount($amount)
	{
		$this->amount = $amount;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDocument()
	{
		return $this->document;
	}

	/**
	 * @param string $document
	 *
	 * @return $this
	 */
	public function setDocument($document)
	{
		$this->document = $document;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getDepId()
	{
		return $this->depId;
	}

	/**
	 * @param int $depId
	 *
	 * @return $this
	 */
	public function setDepId($depId)
	{
		$this->depId = $depId;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getRecipeNo()
	{
		return $this->recipeNo;
	}

	/**
	 * @param int $recipeNo
	 *
	 * @return $this
	 */
	public function setRecipeNo($recipeNo)
	{
		$this->recipeNo = $recipeNo;

		return $this;
	}

	/**
	 * Set user
	 *
	 * @param User $user
	 *
	 * @return Transaction
	 */
	public function setUser(User $user)
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * Get user
	 *
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param Store $store
	 *
	 * @return Transaction
	 */
	public function setStore(Store $store)
	{
		$this->store = $store;

		return $this;
	}

	/**
	 * @param TransactionType $type
	 *
	 * @return Transaction
	 */
	public function setType(TransactionType $type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * @return TransactionType
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param PaymentMethod $paymentMethod
	 *
	 * @return Transaction
	 */
	public function setMethod(PaymentMethod $paymentMethod)
	{
		$this->method = $paymentMethod;

		return $this;
	}

	/**
	 * @return PaymentMethod
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @return Store
	 */
	public function getStore()
	{
		return $this->store;
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
			'store'    => $this->store->getId(),
			'user'     => $this->user->getId(),
			'type'     => $this->type->getId(),
			'method'   => $this->method ? $this->method->getId() : null,
			'date'     => $this->date->format('Y-m-d'),
			'amount'   => $this->amount,
			'document' => $this->document,
			'depId'    => $this->depId,
			'recipeNo' => $this->recipeNo,
		];
	}

	/**
	 * @param int $id
	 *
	 * @return Transaction
	 */
	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}
}
