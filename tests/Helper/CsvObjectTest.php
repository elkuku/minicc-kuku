<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Helper\CsvParser\CsvObject;
use PHPUnit\Framework\TestCase;
use stdClass;

final class CsvObjectTest extends TestCase
{
    public function testHeadVarsDefaultsToEmptyArray(): void
    {
        $csvObject = new CsvObject();

        self::assertSame([], $csvObject->headVars);
    }

    public function testLinesDefaultsToEmptyArray(): void
    {
        $csvObject = new CsvObject();

        self::assertSame([], $csvObject->lines);
    }

    public function testHeadVarsCanBeSet(): void
    {
        $csvObject = new CsvObject();
        $csvObject->headVars = ['name', 'amount', 'date'];

        self::assertSame(['name', 'amount', 'date'], $csvObject->headVars);
    }

    public function testLinesCanBeSet(): void
    {
        $csvObject = new CsvObject();

        $line = new stdClass();
        $line->name = 'Test';

        $csvObject->lines = [$line];

        self::assertCount(1, $csvObject->lines);
        self::assertSame('Test', $csvObject->lines[0]->name);
    }
}
