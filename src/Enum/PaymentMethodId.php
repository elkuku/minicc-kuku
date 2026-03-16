<?php

declare(strict_types=1);

namespace App\Enum;

enum PaymentMethodId: int
{
    case BAR = 1;
    case BANK = 2;
}
