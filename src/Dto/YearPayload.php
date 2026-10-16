<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * Request payload carrying an optional year selector.
 */
final readonly class YearPayload
{
    public function __construct(
        public ?int $year = null,
    ) {}
}
