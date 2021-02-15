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
use UnexpectedValueException;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DepositRepository")
 */
class Deposit implements JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var PaymentMethod
     * @ManyToOne(targetEntity="PaymentMethod")
     */
    private PaymentMethod $entity;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="date", nullable=false)
     */
    private DateTime $date;

    /**
     * @ORM\Column(type="string", length=150, nullable=false)
     */
    private string $document;

    /**
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=false)
     */
    private float $amount;

    public function getId(): int
    {
        return $this->id;
    }

    public function setEntity(PaymentMethod $entity): Deposit
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

    public function setDate(DateTime $date): Deposit
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function setDocument($document): Deposit
    {
        $this->document = $document;

        return $this;
    }

    public function getDocument(): string
    {
        return $this->document;
    }

    public function setAmount($amount): Deposit
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize(): array
    {
        return [
            'id'       => $this->id,
            'amount'   => $this->amount,
            'document' => $this->document,
            'date'     => $this->date->format('Y-m-d'),
        ];
    }
}
