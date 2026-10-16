<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PaymentsAccountantPayload
{
    /**
     * @param array<array-key, mixed> $ids
     */
    public function __construct(
        public ?int $year = null,
        #[Assert\Range(min: 1, max: 12)]
        public ?int $month = null,
        #[Assert\All(constraints: [new Assert\Type(type: 'numeric')])]
        public array $ids = [],
    ) {}

    /**
     * @return list<int>
     */
    public function getIds(): array
    {
        return array_values(array_map(intval(...), array_filter($this->ids, is_numeric(...))));
    }
}
