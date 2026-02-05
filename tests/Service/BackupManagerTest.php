<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\BackupManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class BackupManagerTest extends TestCase
{
    public function testGetDbUrl(): void
    {
        $manager = new BackupManager('postgresql://user:pass@localhost:5432/dbname');

        self::assertSame('postgresql://user:pass@localhost:5432/dbname', $manager->getDbUrl());
    }

    public function testGetBackupCommandPostgresql(): void
    {
        $manager = new BackupManager('postgresql://myuser:mypass@localhost:5432/mydb');

        $command = $manager->getBackupCommand();

        self::assertStringContainsString('pg_dump', $command);
        self::assertStringContainsString('PGPASSWORD=', $command);
        self::assertStringContainsString('localhost', $command);
        self::assertStringContainsString('5432', $command);
        self::assertStringContainsString('myuser', $command);
        self::assertStringContainsString('mydb', $command);
    }

    public function testGetBackupCommandMysql(): void
    {
        $manager = new BackupManager('mysql://myuser:mypass@localhost:3306/mydb');

        $command = $manager->getBackupCommand();

        self::assertStringContainsString('mysqldump', $command);
        self::assertStringContainsString('localhost', $command);
        self::assertStringContainsString('3306', $command);
        self::assertStringContainsString('myuser', $command);
        self::assertStringContainsString('mydb', $command);
    }

    public function testGetBackupCommandMariadb(): void
    {
        $manager = new BackupManager('mariadb://myuser:mypass@localhost:3306/mydb');

        $command = $manager->getBackupCommand();

        self::assertStringContainsString('mariadb-dump', $command);
        self::assertStringContainsString('localhost', $command);
        self::assertStringContainsString('3306', $command);
        self::assertStringContainsString('myuser', $command);
        self::assertStringContainsString('mydb', $command);
    }

    public function testGetBackupCommandThrowsOnEmptyUrl(): void
    {
        $manager = new BackupManager('');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No DATABASE_URL found in environment');

        $manager->getBackupCommand();
    }

    public function testGetBackupCommandThrowsOnInvalidDriver(): void
    {
        $manager = new BackupManager('sqlite://localhost/mydb');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid DB driver');

        $manager->getBackupCommand();
    }

    #[DataProvider('databaseUrlProvider')]
    public function testGetBackupCommandWithVariousUrls(string $url, string $expectedTool): void
    {
        $manager = new BackupManager($url);

        $command = $manager->getBackupCommand();

        self::assertStringContainsString($expectedTool, $command);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function databaseUrlProvider(): array
    {
        return [
            'postgresql standard' => ['postgresql://u:p@h:5432/db', 'pg_dump'],
            'mysql standard' => ['mysql://u:p@h:3306/db', 'mysqldump'],
            'mariadb standard' => ['mariadb://u:p@h:3306/db', 'mariadb-dump'],
        ];
    }

    public function testGetBackupCommandHandlesSpecialCharactersInPassword(): void
    {
        $manager = new BackupManager('postgresql://user:p@ss%40word@localhost:5432/db');

        $command = $manager->getBackupCommand();

        self::assertStringContainsString('pg_dump', $command);
    }

    public function testGetBackupCommandWithDifferentPorts(): void
    {
        $manager = new BackupManager('postgresql://user:pass@localhost:15432/db');

        $command = $manager->getBackupCommand();

        self::assertStringContainsString('15432', $command);
    }

    public function testGetBackupCommandThrowsOnMalformedUrl(): void
    {
        $manager = new BackupManager('https://host:99999999');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid DATABASE_URL format');

        $manager->getBackupCommand();
    }

    public function testPostgresqlPasswordInPgpasswordEnv(): void
    {
        $manager = new BackupManager('postgresql://user:s3cret@localhost:5432/db');

        $command = $manager->getBackupCommand();

        self::assertStringContainsString("PGPASSWORD='s3cret'", $command);
    }

    public function testMysqlPasswordInFlag(): void
    {
        $manager = new BackupManager('mysql://user:s3cret@localhost:3306/db');

        $command = $manager->getBackupCommand();

        self::assertStringContainsString("-p's3cret'", $command);
        self::assertStringNotContainsString('PGPASSWORD', $command);
    }

    public function testMariadbPasswordInFlag(): void
    {
        $manager = new BackupManager('mariadb://user:s3cret@localhost:3306/db');

        $command = $manager->getBackupCommand();

        self::assertStringContainsString("-p's3cret'", $command);
        self::assertStringContainsString('mariadb-dump', $command);
    }

    public function testGetBackupCommandWithNoPassword(): void
    {
        $manager = new BackupManager('postgresql://user@localhost:5432/db');

        $command = $manager->getBackupCommand();

        self::assertStringContainsString("PGPASSWORD=''", $command);
        self::assertStringContainsString("'user'", $command);
    }

    public function testGetBackupCommandExtractsDatabaseName(): void
    {
        $manager = new BackupManager('postgresql://u:p@h:5432/my_database');

        $command = $manager->getBackupCommand();

        self::assertStringContainsString("'my_database'", $command);
    }

    public function testPostgresqlCommandFormat(): void
    {
        $manager = new BackupManager('postgresql://myuser:mypass@dbhost:5432/mydb');

        $command = $manager->getBackupCommand();

        self::assertSame(
            "PGPASSWORD='mypass' pg_dump -h 'dbhost' -p '5432' -U 'myuser' 'mydb'",
            $command,
        );
    }

    public function testMysqlCommandFormat(): void
    {
        $manager = new BackupManager('mysql://myuser:mypass@dbhost:3306/mydb');

        $command = $manager->getBackupCommand();

        self::assertSame(
            "mysqldump -h'dbhost' -P'3306' -u'myuser' -p'mypass' 'mydb'",
            $command,
        );
    }

    public function testMariadbCommandFormat(): void
    {
        $manager = new BackupManager('mariadb://myuser:mypass@dbhost:3306/mydb');

        $command = $manager->getBackupCommand();

        self::assertSame(
            "mariadb-dump -h'dbhost' -P'3306' -u'myuser' -p'mypass' 'mydb'",
            $command,
        );
    }
}
