<?php

declare(strict_types=1);

namespace App\Service;

use LogicException;

class DeployLogParser
{
    /**
     * @return array<string, string>
     */
    public function parse(string $contents): array
    {
        $entries = [];
        $entry = null;
        $dateTime = null;

        foreach (explode("\n", $contents) as $line) {
            $line = trim($line);
            if ($line === '' || $line === '0') {
                continue;
            }

            $this->processLogLine($line, $entry, $dateTime, $entries);
        }

        return $entries;
    }

    /**
     * @param array<string, string> $entries
     */
    private function processLogLine(string $line, ?string &$entry, ?string &$dateTime, array &$entries): void
    {
        if (str_starts_with($line, '>>>==============')) {
            $this->handleEntryStart($entry);

            return;
        }

        if (str_starts_with($line, '<<<===========')) {
            $this->handleEntryEnd($entry, $dateTime, $entries);

            return;
        }

        if ('' === $entry) {
            // The first line contains the dateTime string
            $dateTime = $line;
            $entry = $line."\n";

            return;
        }

        $entry .= $line."\n";
    }

    /**
     * @param-out string $entry
     */
    private function handleEntryStart(?string &$entry): void
    {
        if (!is_null($entry)) {
            throw new LogicException('Entry finished string not found');
        }

        $entry = '';
    }

    /**
     * @param array<string, string> $entries
     * @param-out null $entry
     */
    private function handleEntryEnd(?string &$entry, ?string $dateTime, array &$entries): void
    {
        if (is_null($entry)) {
            throw new LogicException('Entry not started.');
        }

        $entries[(string) $dateTime] = $entry;
        $entry = null;
    }
}
