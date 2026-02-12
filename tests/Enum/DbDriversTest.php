<?php

declare(strict_types=1);

namespace App\Tests\Enum;

use App\Enum\DbDrivers;
use PHPUnit\Framework\TestCase;

final class DbDriversTest extends TestCase
{
    public function testPostgreSQLValue(): void
    {
        $this->assertSame('postgresql', DbDrivers::PostgreSQL->value);
    }

    public function testMySQLValue(): void
    {
        $this->assertSame('mysql', DbDrivers::MySQL->value);
    }

    public function testMariaDBValue(): void
    {
        $this->assertSame('mariadb', DbDrivers::MariaDB->value);
    }

    public function testCasesCount(): void
    {
        $this->assertCount(3, DbDrivers::cases());
    }

    public function testFromPostgresql(): void
    {
        $driver = DbDrivers::from('postgresql');

        $this->assertSame(DbDrivers::PostgreSQL, $driver);
    }

    public function testFromMysql(): void
    {
        $driver = DbDrivers::from('mysql');

        $this->assertSame(DbDrivers::MySQL, $driver);
    }

    public function testFromMariadb(): void
    {
        $driver = DbDrivers::from('mariadb');

        $this->assertSame(DbDrivers::MariaDB, $driver);
    }

    public function testTryFromInvalidReturnsNull(): void
    {
        $driver = DbDrivers::tryFrom('invalid');

        $this->assertNotSame(DbDrivers::PostgreSQL, $driver);
        $this->assertNotSame(DbDrivers::MySQL, $driver);
        $this->assertNotSame(DbDrivers::MariaDB, $driver);
    }

    public function testTryFromValidReturnsEnum(): void
    {
        $driver = DbDrivers::tryFrom('mysql');

        $this->assertSame(DbDrivers::MySQL, $driver);
    }

    public function testEnumNames(): void
    {
        $this->assertSame('PostgreSQL', DbDrivers::PostgreSQL->name);
        $this->assertSame('MySQL', DbDrivers::MySQL->name);
        $this->assertSame('MariaDB', DbDrivers::MariaDB->name);
    }
}
