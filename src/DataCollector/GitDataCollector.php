<?php

declare(strict_types=1);

namespace App\DataCollector;

use App\Helper\GitLoader;
use Override;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @see \App\Tests\DataCollector\GitDataCollectorTest
 */
class GitDataCollector extends AbstractDataCollector
{
    public function __construct(
        private readonly GitLoader $gitLoader,
    ) {
    }

    #[Override]
    public function collect(
        Request $request,
        Response $response,
        ?Throwable $exception = null,
    ): void {
        $this->data = [
            'git_branch' => $this->gitLoader->getBranchName(),
            'last_commit_message' => $this->gitLoader->getLastCommitMessage(),
            'logs' => $this->gitLoader->getLastCommitDetail(),
        ];
    }

    public function getLastCommitMessage(): string
    {
        return $this->data['last_commit_message'];
    }

    #[Override]
    public static function getTemplate(): string
    {
        return 'data_collector/git_data.html.twig';
    }

    #[Override]
    public function reset(): void
    {
        $this->data = [];
    }

    public function getGitBranch(): string
    {
        return $this->data['git_branch'];
    }

    public function getLastCommitAuthor(): string
    {
        return $this->data['logs']['author'];
    }

    public function getLastCommitDate(): string
    {
        return $this->data['logs']['date'];
    }

    public function getSha(): string
    {
        return $this->data['logs']['sha'];
    }
}
