<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class MailRecipientsPayload
{
    /**
     * @param array<int, mixed> $recipients Keys are store IDs
     */
    public function __construct(
        public array $recipients = [],
        public ?int $year = null,
    ) {
    }
}
