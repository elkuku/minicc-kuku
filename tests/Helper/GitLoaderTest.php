<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use RuntimeException;
use Override;
use App\Helper\GitLoader;
use PHPUnit\Framework\TestCase;

final class GitLoaderTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/git_loader_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    public function testGetBranchNameReturnsDefaultWhenNoGitDir(): void
    {
        $gitLoader = new GitLoader($this->tempDir);

        $result = $gitLoader->getBranchName();

        $this->assertSame('no branch name', $result);
    }

    public function testGetBranchNameReadsBranchFromHead(): void
    {
        mkdir($this->tempDir . '/.git', 0777, true);
        file_put_contents($this->tempDir . '/.git/HEAD', "ref: refs/heads/main\n");

        $gitLoader = new GitLoader($this->tempDir);

        $result = $gitLoader->getBranchName();

        $this->assertSame('main', $result);
    }

    public function testGetBranchNameHandlesFeatureBranch(): void
    {
        mkdir($this->tempDir . '/.git', 0777, true);
        file_put_contents($this->tempDir . '/.git/HEAD', "ref: refs/heads/feature/my-feature\n");

        $gitLoader = new GitLoader($this->tempDir);

        $result = $gitLoader->getBranchName();

        $this->assertSame('feature/my-feature', $result);
    }

    public function testGetLastCommitMessageReturnsEmptyWhenNoFile(): void
    {
        $gitLoader = new GitLoader($this->tempDir);

        $result = $gitLoader->getLastCommitMessage();

        $this->assertSame('', $result);
    }

    public function testGetLastCommitMessageReadsFirstLine(): void
    {
        mkdir($this->tempDir . '/.git', 0777, true);
        file_put_contents(
            $this->tempDir . '/.git/COMMIT_EDITMSG',
            "Initial commit\n\nThis is the body of the commit message."
        );

        $gitLoader = new GitLoader($this->tempDir);

        $result = $gitLoader->getLastCommitMessage();

        $this->assertSame('Initial commit', $result);
    }

    public function testGetLastCommitMessageTrimsWhitespace(): void
    {
        mkdir($this->tempDir . '/.git', 0777, true);
        file_put_contents($this->tempDir . '/.git/COMMIT_EDITMSG', "  Fix bug  \n");

        $gitLoader = new GitLoader($this->tempDir);

        $result = $gitLoader->getLastCommitMessage();

        $this->assertSame('Fix bug', $result);
    }

    public function testGetLastCommitDetailReturnsDefaultsWhenNoLogs(): void
    {
        mkdir($this->tempDir . '/.git', 0777, true);

        $gitLoader = new TestableGitLoader($this->tempDir);

        $result = $gitLoader->getLastCommitDetail();

        $this->assertArrayHasKey('author', $result);
        $this->assertArrayHasKey('date', $result);
        $this->assertArrayHasKey('sha', $result);
        $this->assertSame('not defined', $result['author']);
        $this->assertSame('not defined', $result['date']);
    }

    public function testGetLastCommitDetailParsesLogData(): void
    {
        mkdir($this->tempDir . '/.git/logs', 0777, true);
        $logLine = "0000000 abc1234 John Doe <john@example.com> 1700000000 -0100\tcommit: Initial commit\n";
        file_put_contents($this->tempDir . '/.git/logs/HEAD', $logLine);

        $gitLoader = new TestableGitLoader($this->tempDir);

        $result = $gitLoader->getLastCommitDetail();

        $this->assertSame('John Doe', $result['author']);
        $this->assertSame(date('Y/m/d H:i', 1700000000), $result['date']);
        $this->assertSame('abc123', $result['sha']);
    }

    public function testGetLastCommitDetailWithNoMatchingLogFormat(): void
    {
        mkdir($this->tempDir . '/.git/logs', 0777, true);
        file_put_contents($this->tempDir . '/.git/logs/HEAD', "some random text\n");

        $gitLoader = new TestableGitLoader($this->tempDir);

        $result = $gitLoader->getLastCommitDetail();

        $this->assertSame('not defined', $result['author']);
        $this->assertSame('not defined', $result['date']);
        $this->assertSame('abc123', $result['sha']);
    }

    public function testExecCommandThrowsOnFailureWithLastLine(): void
    {
        $gitLoader = new FailingGitLoader($this->tempDir, 'Something went wrong');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Something went wrong');

        $gitLoader->getLastCommitDetail();
    }

    public function testExecCommandThrowsUnknownErrorOnFailureWithoutLastLine(): void
    {
        $gitLoader = new FailingGitLoader($this->tempDir, '');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('An unknown error occurred');

        $gitLoader->getLastCommitDetail();
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir) ?: [], ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }
}

/**
 * Testable version of GitLoader that overrides execCommand to avoid shell execution.
 */
class TestableGitLoader extends GitLoader
{
    #[Override]
    protected function execCommand(string $command): bool|string
    {
        return 'abc123';
    }
}

/**
 * GitLoader that simulates command failure.
 */
class FailingGitLoader extends GitLoader
{
    public function __construct(
        string $rootDir,
        private readonly string $lastLine,
    ) {
        parent::__construct($rootDir);
    }

    #[Override]
    protected function execCommand(string $command): bool|string
    {
        if ($this->lastLine !== '' && $this->lastLine !== '0') {
            throw new RuntimeException($this->lastLine);
        }

        throw new RuntimeException('An unknown error occurred');
    }
}
