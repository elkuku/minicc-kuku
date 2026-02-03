<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Helper\CsvParser\CsvObject;
use App\Helper\CsvParser\CsvParser;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use UnexpectedValueException;

final class CsvParserTest extends TestCase
{
    private CsvParser $parser;

    protected function setUp(): void
    {
        $this->parser = new CsvParser();
    }

    public function testParseCSVBasic(): void
    {
        $contents = [
            'Name,Email,Age',
            'John,john@example.com,30',
            'Jane,jane@example.com,25',
        ];

        $result = $this->parser->parseCSV($contents);

        self::assertInstanceOf(CsvObject::class, $result);
        self::assertSame(['Name', 'Email', 'Age'], $result->headVars);
        self::assertCount(2, $result->lines);
    }

    public function testParseCSVLineData(): void
    {
        $contents = [
            'Name,Email',
            'John,john@example.com',
        ];

        $result = $this->parser->parseCSV($contents);

        self::assertSame('John', $result->lines[0]->name);
        self::assertSame('john@example.com', $result->lines[0]->email);
    }

    public function testParseCSVHeadersAreLowercased(): void
    {
        $contents = [
            'NAME,EMAIL,AGE',
            'John,john@example.com,30',
        ];

        $result = $this->parser->parseCSV($contents);

        self::assertTrue(property_exists($result->lines[0], 'name'));
        self::assertTrue(property_exists($result->lines[0], 'email'));
        self::assertTrue(property_exists($result->lines[0], 'age'));
    }

    public function testParseCSVTrimsWhitespace(): void
    {
        $contents = [
            'Name,Email',
            '  John  ,  john@example.com  ',
        ];

        $result = $this->parser->parseCSV($contents);

        self::assertSame('John', $result->lines[0]->name);
        self::assertSame('john@example.com', $result->lines[0]->email);
    }

    public function testParseCSVWithOuterQuotes(): void
    {
        // Parser only trims outer quotes from the entire line, not individual fields
        $contents = [
            '"Name,Email"',
            '"John,john@example.com"',
        ];

        $result = $this->parser->parseCSV($contents);

        self::assertSame(['Name', 'Email'], $result->headVars);
        self::assertSame('John', $result->lines[0]->name);
    }

    public function testParseCSVThrowsOnEmptyContents(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('CSV file is empty');

        $this->parser->parseCSV([]);
    }

    public function testParseCSVThrowsOnMalformedData(): void
    {
        $contents = [
            'Name,Email',
            'John,john@example.com,ExtraField,AnotherExtra',
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Malformed CSV file.');

        $this->parser->parseCSV($contents);
    }

    public function testParseCSVSingleLine(): void
    {
        $contents = [
            'Name,Email',
            'John,john@example.com',
        ];

        $result = $this->parser->parseCSV($contents);

        self::assertCount(1, $result->lines);
    }

    public function testParseCSVHeaderOnly(): void
    {
        $contents = [
            'Name,Email,Age',
        ];

        $result = $this->parser->parseCSV($contents);

        self::assertSame(['Name', 'Email', 'Age'], $result->headVars);
        self::assertCount(0, $result->lines);
    }

    public function testParseCSVMultipleLines(): void
    {
        $contents = [
            'Id,Name',
            '1,Alice',
            '2,Bob',
            '3,Charlie',
            '4,Diana',
        ];

        $result = $this->parser->parseCSV($contents);

        self::assertCount(4, $result->lines);
        self::assertSame('1', $result->lines[0]->id);
        self::assertSame('Alice', $result->lines[0]->name);
        self::assertSame('4', $result->lines[3]->id);
        self::assertSame('Diana', $result->lines[3]->name);
    }

    public function testCsvObjectStructure(): void
    {
        $csvObject = new CsvObject();

        self::assertSame([], $csvObject->headVars);
        self::assertSame([], $csvObject->lines);
    }

    public function testCsvObjectCanBeModified(): void
    {
        $csvObject = new CsvObject();
        $csvObject->headVars = ['A', 'B'];

        self::assertSame(['A', 'B'], $csvObject->headVars);
    }
}
