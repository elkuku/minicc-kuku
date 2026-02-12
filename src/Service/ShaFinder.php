<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ShaFinder
{
    private string $sha = 'n/a';

    public function __construct(#[Autowire('%kernel.project_dir%')] string $rootDir)
    {
        if (file_exists($rootDir.'/sha.txt')) {
            $this->sha = file_get_contents($rootDir.'/sha.txt') ?: 'n/a';
        } elseif (file_exists($rootDir.'/.git/refs/heads/master')) {
            $this->sha = file_get_contents($rootDir.'/.git/refs/heads/master')
                ?: 'n/a';
        }
    }

    public function getSha(): string
    {
        return $this->sha;
    }
}
