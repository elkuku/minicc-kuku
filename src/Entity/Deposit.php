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
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;
use UnexpectedValueException;
use App\Repository\DepositRepository;

#[Entity(repositoryClass: DepositRepository::class)]
class Deposit implements JsonSerializable
{
    #[Id, GeneratedValue(strategy: 'AUTO')]
    #[Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ManyToOne(targetEntity: PaymentMethod::class)]
    private PaymentMethod $entity;

    #[Column(type: Types::DATE_MUTABLE, nullable: false)]
    private DateTime $date;

    #[Column(type: Types::STRING, length: 150, nullable: false)]
    private string $document;

    #[Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: false)]
    private float $amount;

    #[OneToOne(mappedBy: 'deposit', targetEntity: Transaction::class, cascade: [
        'persist',
        'remove',
    ])]
    private ?Transaction $transaction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setEntity(PaymentMethod $entity): static
    {
        if (1 === $entity->getId()) {
            throw new UnexpectedValueException(
                'The entity with ID "1" is supposed to be the BAR payment method!'
            );
        }

        $this->entity = $entity;

        return $this;
    }

    public function getEntity(): PaymentMethod
    {
        return $this->entity;
    }

    public function setDate(DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function setDocument(string $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function getDocument(): string
    {
        return $this->document;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): self
    {
        // unset the owning side of the relation if necessary
        if ($transaction === null && $this->transaction !== null) {
            $this->transaction->setDeposit(null);
        }

        // set the owning side of the relation if necessary
        if ($transaction !== null && $transaction->getDeposit() !== $this) {
            $transaction->setDeposit($this);
        }

        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array<string, float|int|string|null> data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    #[ArrayShape(['id'       => "int|null",
                  'amount'   => "float",
                  'document' => "string",
                  'date'     => "string",
                  'entity'   => "int|null"
    ])] public function jsonSerialize(): array
    {
        return [
            'id'       => $this->id,
            'amount'   => $this->amount,
            'document' => $this->document,
            'date'     => $this->date->format('Y-m-d'),
            'entity'   => $this->entity->getId(),
        ];
    }
}
