<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\DbDrivers;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use ValueError;

readonly class BackupManager
{
    public function __construct(
        #[Autowire(env: 'DATABASE_URL')] private string $databaseUrl,
    )
    {
    }

    public function getDbUrl(): string
    {
        return $this->databaseUrl;
    }

    public function getBackupCommand(): string
    {
        if (!$this->databaseUrl) {
            throw new RuntimeException('No DATABASE_URL found in environment');
        }

        $parts = parse_url($this->databaseUrl);
        if ($parts === false) {
            throw new RuntimeException('Invalid DATABASE_URL format');
        }

        $scheme = $parts['scheme'] ?? '';

        try {
            $dbDriver = DbDrivers::from($scheme);
        } catch (ValueError) {
            throw new RuntimeException('Invalid DB driver');
        }

        $dbUser = $parts['user'] ?? '';
        $dbPass = $parts['pass'] ?? '';
        $dbHost = $parts['host'] ?? '';
        $dbPort = $parts['port'] ?? '';
        $dbName = ltrim($parts['path'] ?? '', '/');

        return match ($dbDriver) {
            DbDrivers::PostgreSQL => sprintf(
                '%s pg_dump -h %s -p %s -U %s %s',
                sprintf('PGPASSWORD=%s', escapeshellarg($dbPass)),
                escapeshellarg($dbHost),
                escapeshellarg((string)$dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbName)
            ),
            DbDrivers::MySQL => sprintf(
                'mysqldump -h%s -P%s -u%s -p%s %s',
                escapeshellarg($dbHost),
                escapeshellarg((string)$dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName)
            ),
            DbDrivers::MariaDB => sprintf(
                'mariadb-dump -h%s -P%s -u%s -p%s %s',
                escapeshellarg($dbHost),
                escapeshellarg((string)$dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName)
            ),
        };
    }
}
