<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class ContractCreatePayload
{
    public function __construct(
        public ?int $store = null,
        public ?int $user = null,
    ) {
    }
}
