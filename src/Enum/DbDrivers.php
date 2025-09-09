<?php

declare(strict_types=1);

namespace App\Enum;

enum DbDrivers: string
{
    case PostgreSQL = 'postgresql';
    case MySQL = 'mysql';
    case MariaDB = 'mariadb';
}
