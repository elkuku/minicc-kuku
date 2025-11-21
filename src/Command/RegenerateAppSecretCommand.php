<?php

declare(strict_types=1);

namespace App\Command;

use Random\RandomException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'gen-secret',
    description: 'Generate a new secret key',
)]
class RegenerateAppSecretCommand
{
    /**
     * @throws RandomException
     */
    public function __invoke(SymfonyStyle $io): int
    {
        $secret = bin2hex(random_bytes(16));

        $msg = "Your secret key {$secret} \nplease replace this key with your APP_SECRET in .env file";

        $io->success($msg);

        return Command::SUCCESS;
    }
}
