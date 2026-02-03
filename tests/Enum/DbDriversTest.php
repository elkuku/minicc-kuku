<?php

declare(strict_types=1);

namespace App\Tests\Enum;

use App\Enum\DbDrivers;
use PHPUnit\Framework\TestCase;

final class DbDriversTest extends TestCase
{
    public function testPostgreSQLValue(): void
    {
        self::assertSame('postgresql', DbDrivers::PostgreSQL->value);
    }

    public function testMySQLValue(): void
    {
        self::assertSame('mysql', DbDrivers::MySQL->value);
    }

    public function testMariaDBValue(): void
    {
        self::assertSame('mariadb', DbDrivers::MariaDB->value);
    }

    public function testCasesCount(): void
    {
        self::assertCount(3, DbDrivers::cases());
    }

    public function testFromPostgresql(): void
    {
        $driver = DbDrivers::from('postgresql');

        self::assertSame(DbDrivers::PostgreSQL, $driver);
    }

    public function testFromMysql(): void
    {
        $driver = DbDrivers::from('mysql');

        self::assertSame(DbDrivers::MySQL, $driver);
    }

    public function testFromMariadb(): void
    {
        $driver = DbDrivers::from('mariadb');

        self::assertSame(DbDrivers::MariaDB, $driver);
    }

    public function testTryFromInvalidReturnsNull(): void
    {
        $driver = DbDrivers::tryFrom('invalid');

        self::assertNotSame(DbDrivers::PostgreSQL, $driver);
        self::assertNotSame(DbDrivers::MySQL, $driver);
        self::assertNotSame(DbDrivers::MariaDB, $driver);
    }

    public function testTryFromValidReturnsEnum(): void
    {
        $driver = DbDrivers::tryFrom('mysql');

        self::assertSame(DbDrivers::MySQL, $driver);
    }

    public function testEnumNames(): void
    {
        self::assertSame('PostgreSQL', DbDrivers::PostgreSQL->name);
        self::assertSame('MySQL', DbDrivers::MySQL->name);
        self::assertSame('MariaDB', DbDrivers::MariaDB->name);
    }
}
