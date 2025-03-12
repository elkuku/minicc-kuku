<?php

namespace App\Tests\Twig;

use App\Repository\UserRepository;
use App\Service\ShaFinder;
use App\Service\TaxService;
use App\Twig\Runtime\AppExtensionRuntime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TwigExtensionRuntimeTest extends WebTestCase
{
    private AppExtensionRuntime $twigExtensionRuntime;

    protected function setUp(): void
    {
        static::createClient();
        $this->twigExtensionRuntime = new AppExtensionRuntime(
            $this->createMock(UserRepository::class),
            $this->createMock(ShaFinder::class),
            new TaxService(12),
        );
    }

    public function testX(): void
    {
        self::assertSame(112.0, $this->twigExtensionRuntime->getValueWithTax(100));
        self::assertSame(12.0, $this->twigExtensionRuntime->getTaxFromTotal(112));
    }
}
