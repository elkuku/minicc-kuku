<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\ShaFinder;
use PHPUnit\Framework\TestCase;

final class ShaFinderTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/sha_finder_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    public function testGetShaReturnsNaWhenNoFiles(): void
    {
        $shaFinder = new ShaFinder($this->tempDir);

        $this->assertSame('n/a', $shaFinder->getSha());
    }

    public function testGetShaReadsFromShaTxt(): void
    {
        file_put_contents($this->tempDir . '/sha.txt', 'abc123def');

        $shaFinder = new ShaFinder($this->tempDir);

        $this->assertSame('abc123def', $shaFinder->getSha());
    }

    public function testGetShaReadsFromGitHeadWhenNoShaTxt(): void
    {
        mkdir($this->tempDir . '/.git/refs/heads', 0777, true);
        file_put_contents($this->tempDir . '/.git/refs/heads/master', 'git-sha-456');

        $shaFinder = new ShaFinder($this->tempDir);

        $this->assertSame('git-sha-456', $shaFinder->getSha());
    }

    public function testGetShaPrefersShaTxtOverGitHead(): void
    {
        file_put_contents($this->tempDir . '/sha.txt', 'sha-txt-value');
        mkdir($this->tempDir . '/.git/refs/heads', 0777, true);
        file_put_contents($this->tempDir . '/.git/refs/heads/master', 'git-value');

        $shaFinder = new ShaFinder($this->tempDir);

        $this->assertSame('sha-txt-value', $shaFinder->getSha());
    }

    public function testGetShaReturnsNaForEmptyShaTxt(): void
    {
        file_put_contents($this->tempDir . '/sha.txt', '');

        $shaFinder = new ShaFinder($this->tempDir);

        $this->assertSame('n/a', $shaFinder->getSha());
    }

    public function testGetShaReturnsNaForEmptyGitHead(): void
    {
        mkdir($this->tempDir . '/.git/refs/heads', 0777, true);
        file_put_contents($this->tempDir . '/.git/refs/heads/master', '');

        $shaFinder = new ShaFinder($this->tempDir);

        $this->assertSame('n/a', $shaFinder->getSha());
    }

    public function testGetShaWithWhitespace(): void
    {
        file_put_contents($this->tempDir . '/sha.txt', "  abc123  \n");

        $shaFinder = new ShaFinder($this->tempDir);

        // file_get_contents preserves whitespace
        $this->assertSame("  abc123  \n", $shaFinder->getSha());
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
