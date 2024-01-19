<?php

namespace App\DataCollector;

use App\Helper\GitLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

class GitDataCollector extends DataCollector
{
    public function __construct(
        private readonly GitLoader $gitLoader
    )
    {
    }

    public function collect(
        Request $request,
        Response $response,
        Throwable $exception = null
    ): void {
        $this->data = [
            'git_branch' => $this->gitLoader->getBranchName(),
            'last_commit_message' => $this->gitLoader->getLastCommitMessage(),
            'logs' => $this->gitLoader->getLastCommitDetail(),
        ];
    }

    public function getName(): string
    {
        return 'app.git_data_collector';
    }

    public function reset(): void
    {
        $this->data = [];
    }

    public function getGitBranch(): string
    {
        return $this->data['git_branch'];
    }

    public function getLastCommitMessage(): string
    {
        return $this->data['last_commit_message'];
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
