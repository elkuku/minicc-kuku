<?php
/**
 * Created by PhpStorm.
 * User: elkuku
 * Date: 19.03.17
 * Time: 12:40
 */

namespace App\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use JsonSerializable;
use App\Repository\TransactionRepository;

#[Entity(repositoryClass: TransactionRepository::class)]
class Transaction implements JsonSerializable
{
    #[Id, GeneratedValue(strategy: 'AUTO')]
    #[Column(type: Types::INTEGER)]
    protected ?int $id = null;

    #[ManyToOne(targetEntity: 'Store')]
    #[JoinColumn(name: 'store_id', referencedColumnName: 'id', nullable: false)]
    protected Store $store;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    protected User $user;

    /**
     * The type
     * Alquiler, Pago, etc.
     */
    #[ManyToOne(targetEntity: 'TransactionType')]
    #[JoinColumn(name: 'type_id', referencedColumnName: 'id', nullable: false)]
    private TransactionType $type;

    /**
     * The method
     * Bar, bank, etc.
     */
    #[ManyToOne(targetEntity: 'PaymentMethod')]
    #[JoinColumn(name: 'method_id', referencedColumnName: 'id', nullable: false)]
    private PaymentMethod $method;

    #[Column(type: Types::DATE_MUTABLE, nullable: false)]
    private DateTime $date;

    #[Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: false)]
    private string $amount = '0';

    #[Column(type: Types::INTEGER, length: 20, nullable: true)]
    private int $document;

    /**
     * @deprecated
     */
    #[Column(type: Types::INTEGER, nullable: true)]
    private $depId = null;

    #[OneToOne(inversedBy: 'transaction', targetEntity: Deposit::class, cascade: [
        'persist',
        'remove',
    ])]
    private ?Deposit $deposit;

    #[Column(type: Types::INTEGER, nullable: true)]
    private ?int $recipeNo = 0;

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

    public function getDepId(): ?int
    {
        return $this->depId;
    }

    public function setDepId(int $depId): self
    {
        $this->depId = $depId;

        return $this;
    }

    public function getRecipeNo(): ?int
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

    public function getDeposit(): ?Deposit
    {
        return $this->deposit;
    }

    public function setDeposit(?Deposit $deposit): self
    {
        $this->deposit = $deposit;

        return $this;
    }

    public function jsonSerialize(): array
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
