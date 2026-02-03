<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Twig\Extension\AppExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class AppExtensionTest extends TestCase
{
    private AppExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new AppExtension();
    }

    public function testGetFiltersReturnsArray(): void
    {
        $filters = $this->extension->getFilters();

        self::assertNotEmpty($filters);

        $filterNames = array_map(fn(TwigFilter $f): string => $f->getName(), $filters);
        self::assertContains('invert', $filterNames);
        self::assertContains('cast_to_array', $filterNames);
        self::assertContains('short_name', $filterNames);
        self::assertContains('conIva', $filterNames);
        self::assertContains('taxFromTotal', $filterNames);
    }

    public function testGetFunctionsReturnsArray(): void
    {
        $functions = $this->extension->getFunctions();

        self::assertNotEmpty($functions);

        $functionNames = array_map(fn(TwigFunction $f): string => $f->getName(), $functions);
        self::assertContains('getSHA', $functionNames);
        self::assertContains('findSystemUsers', $functionNames);
        self::assertContains('getCurrentYear', $functionNames);
    }

    #[DataProvider('invertFilterProvider')]
    public function testInvertFilter(int|float $input, int|float $expected): void
    {
        $result = $this->extension->invertFilter($input);

        self::assertSame($expected, $result);
    }

    /**
     * @return array<string, array{int|float, int|float}>
     */
    public static function invertFilterProvider(): array
    {
        return [
            'positive int' => [100, -100],
            'negative int' => [-100, 100],
            'zero int' => [0, 0],
            'positive float' => [50.5, -50.5],
            'negative float' => [-50.5, 50.5],
            'zero float' => [0.0, 0],
        ];
    }

    #[DataProvider('shortNameProvider')]
    public function testShortName(string $longName, string $expected): void
    {
        $result = $this->extension->shortName($longName);

        self::assertSame($expected, $result);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function shortNameProvider(): array
    {
        return [
            'two parts' => ['Juan Perez', 'Juan Perez'],
            'three parts' => ['Juan José Perez', 'Juan Perez'],
            'four parts' => ['Juan José Perez Pillo', 'Juan Perez'],
            'single name' => ['Juan', 'Juan'],
            'five parts' => ['Juan José Perez Pillo Garcia', 'Juan José Perez Pillo Garcia'],
            'empty string' => ['', ''],
        ];
    }

    public function testObjectFilterConvertsObjectToArray(): void
    {
        $object = new stdClass();
        $object->name = 'Test';
        $object->value = 123;

        $result = $this->extension->objectFilter($object);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('value', $result);
        self::assertSame('Test', $result['name']);
        self::assertSame(123, $result['value']);
    }

    public function testObjectFilterWithComplexObject(): void
    {
        $object = new class {
            public string $publicProp = 'public';

            protected string $protectedProp = 'protected';
        };

        $result = $this->extension->objectFilter($object);

        self::assertArrayHasKey('publicProp', $result);
        self::assertSame('public', $result['publicProp']);
    }

    public function testGetCurrentYearReturnsCurrentYear(): void
    {
        $expectedYear = (int) date('Y');

        $result = $this->extension->getCurrentYear();

        self::assertSame($expectedYear, $result);
    }

    public function testGetCurrentYearReturnsReasonableValue(): void
    {
        $result = $this->extension->getCurrentYear();

        self::assertGreaterThan(2020, $result);
    }
}
