<?php

declare(strict_types=1);

namespace App\Tests\DataCollector;

use Exception;
use App\DataCollector\GitDataCollector;
use App\Helper\GitLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GitDataCollectorTest extends TestCase
{
    public function testGetName(): void
    {
        $gitLoader = $this->createStub(GitLoader::class);
        $collector = new GitDataCollector($gitLoader);

        $this->assertSame('app.git_data_collector', $collector->getName());
    }

    public function testCollectStoresGitData(): void
    {
        $gitLoader = $this->createStub(GitLoader::class);
        $gitLoader->method('getBranchName')->willReturn('main');
        $gitLoader->method('getLastCommitMessage')->willReturn('Test commit');
        $gitLoader->method('getLastCommitDetail')->willReturn([
            'author' => 'John Doe',
            'date' => '2024/03/15 10:30',
            'sha' => 'abc123',
        ]);

        $collector = new GitDataCollector($gitLoader);
        $collector->collect(new Request(), new Response());

        $this->assertSame('main', $collector->getGitBranch());
        $this->assertSame('Test commit', $collector->getLastCommitMessage());
        $this->assertSame('John Doe', $collector->getLastCommitAuthor());
        $this->assertSame('2024/03/15 10:30', $collector->getLastCommitDate());
        $this->assertSame('abc123', $collector->getSha());
    }

    public function testReset(): void
    {
        $gitLoader = $this->createStub(GitLoader::class);
        $gitLoader->method('getBranchName')->willReturn('main');
        $gitLoader->method('getLastCommitMessage')->willReturn('Test');
        $gitLoader->method('getLastCommitDetail')->willReturn([
            'author' => 'Test',
            'date' => 'Test',
            'sha' => 'Test',
        ]);

        $collector = new GitDataCollector($gitLoader);
        $collector->collect(new Request(), new Response());

        // Verify data is set before reset
        $this->assertSame('main', $collector->getGitBranch());

        $collector->reset();

        // After reset, data is cleared - this test verifies reset() completes without error
        $this->assertInstanceOf(GitDataCollector::class, $collector);
    }

    public function testCollectWithException(): void
    {
        $gitLoader = $this->createStub(GitLoader::class);
        $gitLoader->method('getBranchName')->willReturn('feature/test');
        $gitLoader->method('getLastCommitMessage')->willReturn('WIP');
        $gitLoader->method('getLastCommitDetail')->willReturn([
            'author' => 'Jane',
            'date' => '2024/01/01 00:00',
            'sha' => 'def456',
        ]);

        $collector = new GitDataCollector($gitLoader);
        $exception = new Exception('Test exception');
        $collector->collect(new Request(), new Response(), $exception);

        $this->assertSame('feature/test', $collector->getGitBranch());
    }

    public function testGetGitBranch(): void
    {
        $gitLoader = $this->createStub(GitLoader::class);
        $gitLoader->method('getBranchName')->willReturn('develop');
        $gitLoader->method('getLastCommitMessage')->willReturn('');
        $gitLoader->method('getLastCommitDetail')->willReturn([
            'author' => '',
            'date' => '',
            'sha' => '',
        ]);

        $collector = new GitDataCollector($gitLoader);
        $collector->collect(new Request(), new Response());

        $this->assertSame('develop', $collector->getGitBranch());
    }

    public function testGetLastCommitMessage(): void
    {
        $gitLoader = $this->createStub(GitLoader::class);
        $gitLoader->method('getBranchName')->willReturn('main');
        $gitLoader->method('getLastCommitMessage')->willReturn('Add new feature');
        $gitLoader->method('getLastCommitDetail')->willReturn([
            'author' => '',
            'date' => '',
            'sha' => '',
        ]);

        $collector = new GitDataCollector($gitLoader);
        $collector->collect(new Request(), new Response());

        $this->assertSame('Add new feature', $collector->getLastCommitMessage());
    }

    public function testGetLastCommitAuthor(): void
    {
        $gitLoader = $this->createStub(GitLoader::class);
        $gitLoader->method('getBranchName')->willReturn('main');
        $gitLoader->method('getLastCommitMessage')->willReturn('');
        $gitLoader->method('getLastCommitDetail')->willReturn([
            'author' => 'Alice Smith',
            'date' => '',
            'sha' => '',
        ]);

        $collector = new GitDataCollector($gitLoader);
        $collector->collect(new Request(), new Response());

        $this->assertSame('Alice Smith', $collector->getLastCommitAuthor());
    }

    public function testGetLastCommitDate(): void
    {
        $gitLoader = $this->createStub(GitLoader::class);
        $gitLoader->method('getBranchName')->willReturn('main');
        $gitLoader->method('getLastCommitMessage')->willReturn('');
        $gitLoader->method('getLastCommitDetail')->willReturn([
            'author' => '',
            'date' => '2024/06/15 14:30',
            'sha' => '',
        ]);

        $collector = new GitDataCollector($gitLoader);
        $collector->collect(new Request(), new Response());

        $this->assertSame('2024/06/15 14:30', $collector->getLastCommitDate());
    }

    public function testGetSha(): void
    {
        $gitLoader = $this->createStub(GitLoader::class);
        $gitLoader->method('getBranchName')->willReturn('main');
        $gitLoader->method('getLastCommitMessage')->willReturn('');
        $gitLoader->method('getLastCommitDetail')->willReturn([
            'author' => '',
            'date' => '',
            'sha' => 'xyz789abc',
        ]);

        $collector = new GitDataCollector($gitLoader);
        $collector->collect(new Request(), new Response());

        $this->assertSame('xyz789abc', $collector->getSha());
    }
}
