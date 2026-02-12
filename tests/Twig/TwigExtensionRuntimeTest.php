<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Repository\UserRepository;
use App\Service\ShaFinder;
use App\Service\TaxService;
use App\Twig\Runtime\AppExtensionRuntime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TwigExtensionRuntimeTest extends WebTestCase
{
    private AppExtensionRuntime $twigExtensionRuntime;

    protected function setUp(): void
    {
        static::createClient();
        $this->twigExtensionRuntime = new AppExtensionRuntime(
            $this->createStub(UserRepository::class),
            $this->createStub(ShaFinder::class),
            new TaxService(12),
        );
    }

    public function testX(): void
    {
        $this->assertEqualsWithDelta(112.0, $this->twigExtensionRuntime->getValueWithTax(100), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(12.0, $this->twigExtensionRuntime->getTaxFromTotal(112), PHP_FLOAT_EPSILON);
    }
}
