<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Payment rows submitted column-wise: each array holds one value per row.
 */
final readonly class PayDayPayments
{
    /**
     * @param list<string> $date
     * @param list<string> $store
     * @param list<string> $method
     * @param list<string> $recipe
     * @param list<string> $document
     * @param list<string> $deposit
     * @param list<string> $amount
     * @param list<string> $comment
     */
    public function __construct(
        #[Assert\All(constraints: [new Assert\Date()])]
        public array $date = [],
        public array $store = [],
        public array $method = [],
        public array $recipe = [],
        public array $document = [],
        public array $deposit = [],
        public array $amount = [],
        public array $comment = [],
    ) {}
}
