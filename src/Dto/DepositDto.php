<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Deposit;
use JsonSerializable;

final readonly class DepositDto implements JsonSerializable
{
    public function __construct(
        public ?int $id,
        public string $amount,
        public string $document,
        public string $date,
        public ?int $entity,
    ) {}

    public static function fromDeposit(Deposit $deposit): self
    {
        return new self(
            id: $deposit->getId(),
            amount: $deposit->getAmount(),
            document: $deposit->getDocument(),
            date: $deposit->getDate()->format('Y-m-d'),
            entity: $deposit->getEntity()->getId(),
        );
    }

    /**
     * @return array{id: int|null, amount: string, document: string, date: string, entity: int|null}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'document' => $this->document,
            'date' => $this->date,
            'entity' => $this->entity,
        ];
    }
}
