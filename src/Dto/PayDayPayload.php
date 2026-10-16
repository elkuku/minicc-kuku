<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PayDayPayload
{
    public function __construct(
        #[Assert\Valid]
        public ?PayDayPayments $payments = null,
    ) {
    }
}
