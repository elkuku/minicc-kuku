<?php
/**
 * Created by PhpStorm.
 * User: test
 * Date: 08/07/18
 * Time: 18:00
 */

namespace App\Helper;

use RuntimeException;
use function is_array;

class GitLoader
{
    public function __construct(private readonly string $rootDir)
    {
    }

    public function getBranchName(): string
    {
        $gitHeadFile = $this->rootDir.'/.git/HEAD';
        $branchName = 'no branch name';

        $stringFromFile = file_exists($gitHeadFile)
            ? file($gitHeadFile, FILE_USE_INCLUDE_PATH) : '';

        if (is_array($stringFromFile)) {
            // Get the string from the array
            $firstLine = $stringFromFile[0];

            // Separate out by the "/" in the string
            $explodedString = explode('/', $firstLine, 3);

            $branchName = trim($explodedString[2]);
        }

        return $branchName;
    }

    public function getLastCommitMessage(): string
    {
        $gitCommitMessageFile = $this->rootDir.'/.git/COMMIT_EDITMSG';
        $commitMessage = file_exists($gitCommitMessageFile)
            ? file($gitCommitMessageFile, FILE_USE_INCLUDE_PATH) : '';

        return is_array($commitMessage) ? trim($commitMessage[0]) : '';
    }

    /**
     * @return array<string, mixed>
     */
    public function getLastCommitDetail(): array
    {
        $gitLogFile = $this->rootDir.'/.git/logs/HEAD';
        $gitLogs = file_exists($gitLogFile)
            ? file($gitLogFile, FILE_USE_INCLUDE_PATH) : [];
        $sha = trim(
            (string)$this->execCommand(
                'cd '.$this->rootDir.' && git rev-parse --short HEAD'
            )
        );

        if ($gitLogs) {
            preg_match(
                "/([\w]+) ([\w]+) ([\w\s]+) (<[\w.@]+>) ([\d]+) ([\d-]+)\tcommit: ([\w\s]+)/",
                (string)end($gitLogs),
                $matches
            );
        }

        $logs = [];

        $logs['author'] = $matches[3] ?? 'not defined';
        $logs['date'] = isset($matches[5]) ? date('Y/m/d H:i', (int)$matches[5])
            : 'not defined';
        $logs['sha'] = $sha;

        return $logs;
    }

    protected function execCommand(string $command): bool|string
    {
        ob_start();
        $lastLine = system($command, $status);

        if ($status) {
            // Command exited with a status != 0
            if ($lastLine) {
                throw new RuntimeException($lastLine);
            }

            throw new RuntimeException('An unknown error occurred');
        }

        ob_end_clean();

        return $lastLine;
    }
}
