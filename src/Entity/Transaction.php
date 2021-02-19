<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Entity;

use DateTime;
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
    protected ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Store")
     */
    protected Store $store;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected User $user;

    /**
     * The type
     * Alquiler, Pago, etc.
     *
     * @ManyToOne(targetEntity="TransactionType")
     */
    private TransactionType $type;

    /**
     * The method
     * Bar, bank, etc.
     *
     * @ManyToOne(targetEntity="PaymentMethod")
     */
    private PaymentMethod $method;

    /**
     * @ORM\Column(type="date", nullable=false)
     */
    private DateTime $date;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=false)
     */
    private string $amount = '0';

    /**
     * @ORM\Column(type="integer", length=20, nullable=true)
     */
    private int $document;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $depId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $recipeNo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDocument(): int
    {
        return $this->document;
    }

    public function setDocument(int $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function getDepId(): int
    {
        return $this->depId;
    }

    public function setDepId(int $depId): self
    {
        $this->depId = $depId;

        return $this;
    }

    public function getRecipeNo(): int
    {
        return $this->recipeNo;
    }

    public function setRecipeNo(int $recipeNo): self
    {
        $this->recipeNo = $recipeNo;

        return $this;
    }

    public function setUser(User $user): Transaction
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setStore(Store $store): Transaction
    {
        $this->store = $store;

        return $this;
    }

    public function setType(TransactionType $type): Transaction
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function setMethod(PaymentMethod $paymentMethod): Transaction
    {
        $this->method = $paymentMethod;

        return $this;
    }

    public function getMethod(): PaymentMethod
    {
        return $this->method;
    }

    public function getStore(): Store
    {
        return $this->store;
    }

    /**
     * Specify data which should be serialized to JSON
     *
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
            'method'   => $this->method->getId() ?: null,
            'date'     => $this->date->format('Y-m-d'),
            'amount'   => $this->amount,
            'document' => $this->document,
            'depId'    => $this->depId,
            'recipeNo' => $this->recipeNo,
        ];
    }

    public function setId(int $id): Transaction
    {
        $this->id = $id;

        return $this;
    }
}
