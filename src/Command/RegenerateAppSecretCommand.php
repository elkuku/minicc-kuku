<?php

namespace App\Command;

use Random\RandomException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'gen-secret',
    description: 'Generate a new secret key',
)]
class RegenerateAppSecretCommand extends Command
{
    /**
     * @throws RandomException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $secret = bin2hex(random_bytes(16));

        $msg = "Your secret key {$secret} \nplease replace this key with your APP_SECRET in .env file";

        $io->success($msg);

        return Command::SUCCESS;
    }
}
