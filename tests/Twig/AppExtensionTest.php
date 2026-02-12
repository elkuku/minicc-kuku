<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use Iterator;
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

        $this->assertNotEmpty($filters);

        $filterNames = array_map(fn(TwigFilter $f): string => $f->getName(), $filters);
        $this->assertContains('invert', $filterNames);
        $this->assertContains('cast_to_array', $filterNames);
        $this->assertContains('short_name', $filterNames);
        $this->assertContains('conIva', $filterNames);
        $this->assertContains('taxFromTotal', $filterNames);
    }

    public function testGetFunctionsReturnsArray(): void
    {
        $functions = $this->extension->getFunctions();

        $this->assertNotEmpty($functions);

        $functionNames = array_map(fn(TwigFunction $f): string => $f->getName(), $functions);
        $this->assertContains('getSHA', $functionNames);
        $this->assertContains('findSystemUsers', $functionNames);
        $this->assertContains('getCurrentYear', $functionNames);
    }

    #[DataProvider('invertFilterProvider')]
    public function testInvertFilter(int|float $input, int|float $expected): void
    {
        $result = $this->extension->invertFilter($input);

        $this->assertSame($expected, $result);
    }

    /**
     * @return Iterator<string, array{(float | int), (float | int)}>
     */
    public static function invertFilterProvider(): Iterator
    {
        yield 'positive int' => [100, -100];
        yield 'negative int' => [-100, 100];
        yield 'zero int' => [0, 0];
        yield 'positive float' => [50.5, -50.5];
        yield 'negative float' => [-50.5, 50.5];
        yield 'zero float' => [0.0, 0];
    }

    #[DataProvider('shortNameProvider')]
    public function testShortName(string $longName, string $expected): void
    {
        $result = $this->extension->shortName($longName);

        $this->assertSame($expected, $result);
    }

    /**
     * @return Iterator<string, array{string, string}>
     */
    public static function shortNameProvider(): Iterator
    {
        yield 'two parts' => ['Juan Perez', 'Juan Perez'];
        yield 'three parts' => ['Juan José Perez', 'Juan Perez'];
        yield 'four parts' => ['Juan José Perez Pillo', 'Juan Perez'];
        yield 'single name' => ['Juan', 'Juan'];
        yield 'five parts' => ['Juan José Perez Pillo Garcia', 'Juan José Perez Pillo Garcia'];
        yield 'empty string' => ['', ''];
    }

    public function testObjectFilterConvertsObjectToArray(): void
    {
        $object = new stdClass();
        $object->name = 'Test';
        $object->value = 123;

        $result = $this->extension->objectFilter($object);

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('value', $result);
        $this->assertSame('Test', $result['name']);
        $this->assertSame(123, $result['value']);
    }

    public function testObjectFilterWithComplexObject(): void
    {
        $object = new class {
            public string $publicProp = 'public';

            protected string $protectedProp = 'protected';
        };

        $result = $this->extension->objectFilter($object);

        $this->assertArrayHasKey('publicProp', $result);
        $this->assertSame('public', $result['publicProp']);
    }

    public function testGetCurrentYearReturnsCurrentYear(): void
    {
        $expectedYear = (int) date('Y');

        $result = $this->extension->getCurrentYear();

        $this->assertSame($expectedYear, $result);
    }

    public function testGetCurrentYearReturnsReasonableValue(): void
    {
        $result = $this->extension->getCurrentYear();

        $this->assertGreaterThan(2020, $result);
    }
}
