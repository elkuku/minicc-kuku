<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * Request payload carrying a local URL to redirect to after the action.
 */
final readonly class RedirectPayload
{
    public function __construct(
        public string $view = '',
    ) {
    }
}
