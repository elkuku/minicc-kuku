<?php

declare(strict_types=1);

namespace App\Helper\CsvParser;

use stdClass;

class CsvObject
{
    /**
     * @var array<string>
     */
    public array $headVars = [];

    /**
     * @var array<int, stdClass>
     */
    public array $lines = [];
}
