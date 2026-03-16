<?php

declare(strict_types=1);

namespace App\Service;

final class MailBatchResult
{
    /** @var list<mixed> */
    private array $successes = [];

    /** @var list<string> */
    private array $failures = [];

    public function addSuccess(mixed $storeId): void
    {
        $this->successes[] = $storeId;
    }

    public function addFailure(string $message): void
    {
        $this->failures[] = $message;
    }

    /** @return list<mixed> */
    public function getSuccesses(): array
    {
        return $this->successes;
    }

    /** @return list<string> */
    public function getFailures(): array
    {
        return $this->failures;
    }

    public function hasSuccesses(): bool
    {
        return $this->successes !== [];
    }

    public function hasFailures(): bool
    {
        return $this->failures !== [];
    }
}
