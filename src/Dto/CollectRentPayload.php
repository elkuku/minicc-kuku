<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CollectRentPayload
{
    /**
     * @param array<int|string, string> $values Rent amounts keyed by store ID
     * @param array<int|string, string> $users  User IDs keyed by store ID
     */
    public function __construct(
        public array $values = [],
        #[Assert\All(constraints: [new Assert\Type(type: 'numeric')])]
        public array $users = [],
        #[SerializedName('date_cobro')]
        #[Assert\Date]
        public string $date = '',
    ) {
    }
}
