<?php

declare(strict_types=1);

namespace App\Tests\Service;

use Iterator;
use App\Service\BackupManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class BackupManagerTest extends TestCase
{
    public function testGetDbUrl(): void
    {
        $manager = new BackupManager('postgresql://user:pass@localhost:5432/dbname');

        $this->assertSame('postgresql://user:pass@localhost:5432/dbname', $manager->getDbUrl());
    }

    public function testGetBackupCommandPostgresql(): void
    {
        $manager = new BackupManager('postgresql://myuser:mypass@localhost:5432/mydb');

        $command = $manager->getBackupCommand();

        $this->assertStringContainsString('pg_dump', $command);
        $this->assertStringContainsString('PGPASSWORD=', $command);
        $this->assertStringContainsString('localhost', $command);
        $this->assertStringContainsString('5432', $command);
        $this->assertStringContainsString('myuser', $command);
        $this->assertStringContainsString('mydb', $command);
    }

    public function testGetBackupCommandMysql(): void
    {
        $manager = new BackupManager('mysql://myuser:mypass@localhost:3306/mydb');

        $command = $manager->getBackupCommand();

        $this->assertStringContainsString('mysqldump', $command);
        $this->assertStringContainsString('localhost', $command);
        $this->assertStringContainsString('3306', $command);
        $this->assertStringContainsString('myuser', $command);
        $this->assertStringContainsString('mydb', $command);
    }

    public function testGetBackupCommandMariadb(): void
    {
        $manager = new BackupManager('mariadb://myuser:mypass@localhost:3306/mydb');

        $command = $manager->getBackupCommand();

        $this->assertStringContainsString('mariadb-dump', $command);
        $this->assertStringContainsString('localhost', $command);
        $this->assertStringContainsString('3306', $command);
        $this->assertStringContainsString('myuser', $command);
        $this->assertStringContainsString('mydb', $command);
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

        $this->assertStringContainsString($expectedTool, $command);
    }

    /**
     * @return Iterator<string, array{string, string}>
     */
    public static function databaseUrlProvider(): Iterator
    {
        yield 'postgresql standard' => ['postgresql://u:p@h:5432/db', 'pg_dump'];
        yield 'mysql standard' => ['mysql://u:p@h:3306/db', 'mysqldump'];
        yield 'mariadb standard' => ['mariadb://u:p@h:3306/db', 'mariadb-dump'];
    }

    public function testGetBackupCommandHandlesSpecialCharactersInPassword(): void
    {
        $manager = new BackupManager('postgresql://user:p@ss%40word@localhost:5432/db');

        $command = $manager->getBackupCommand();

        $this->assertStringContainsString('pg_dump', $command);
    }

    public function testGetBackupCommandWithDifferentPorts(): void
    {
        $manager = new BackupManager('postgresql://user:pass@localhost:15432/db');

        $command = $manager->getBackupCommand();

        $this->assertStringContainsString('15432', $command);
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

        $this->assertStringContainsString("PGPASSWORD='s3cret'", $command);
    }

    public function testMysqlPasswordInFlag(): void
    {
        $manager = new BackupManager('mysql://user:s3cret@localhost:3306/db');

        $command = $manager->getBackupCommand();

        $this->assertStringContainsString("-p's3cret'", $command);
        $this->assertStringNotContainsString('PGPASSWORD', $command);
    }

    public function testMariadbPasswordInFlag(): void
    {
        $manager = new BackupManager('mariadb://user:s3cret@localhost:3306/db');

        $command = $manager->getBackupCommand();

        $this->assertStringContainsString("-p's3cret'", $command);
        $this->assertStringContainsString('mariadb-dump', $command);
    }

    public function testGetBackupCommandWithNoPassword(): void
    {
        $manager = new BackupManager('postgresql://user@localhost:5432/db');

        $command = $manager->getBackupCommand();

        $this->assertStringContainsString("PGPASSWORD=''", $command);
        $this->assertStringContainsString("'user'", $command);
    }

    public function testGetBackupCommandExtractsDatabaseName(): void
    {
        $manager = new BackupManager('postgresql://u:p@h:5432/my_database');

        $command = $manager->getBackupCommand();

        $this->assertStringContainsString("'my_database'", $command);
    }

    public function testPostgresqlCommandFormat(): void
    {
        $manager = new BackupManager('postgresql://myuser:mypass@dbhost:5432/mydb');

        $command = $manager->getBackupCommand();

        $this->assertSame("PGPASSWORD='mypass' pg_dump -h 'dbhost' -p '5432' -U 'myuser' 'mydb'", $command);
    }

    public function testMysqlCommandFormat(): void
    {
        $manager = new BackupManager('mysql://myuser:mypass@dbhost:3306/mydb');

        $command = $manager->getBackupCommand();

        $this->assertSame("mysqldump --no-tablespaces -h'dbhost' -P'3306' -u'myuser' -p'mypass' 'mydb'", $command);
    }

    public function testMariadbCommandFormat(): void
    {
        $manager = new BackupManager('mariadb://myuser:mypass@dbhost:3306/mydb');

        $command = $manager->getBackupCommand();

        $this->assertSame("mariadb-dump --no-tablespaces -h'dbhost' -P'3306' -u'myuser' -p'mypass' 'mydb'", $command);
    }
}
