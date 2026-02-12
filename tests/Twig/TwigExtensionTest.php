<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Twig\Extension\AppExtension;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TwigExtensionTest extends WebTestCase
{
    private AppExtension $twigExtension;

    protected function setUp(): void
    {
        static::createClient();
        $this->twigExtension = new AppExtension();
    }


    public function testShortName(): void
    {
        $this->assertSame('Juan Perez', $this->twigExtension->shortName('Juan Perez'));

        $this->assertSame('Juan Perez', $this->twigExtension->shortName('Juan José Perez'));

        $this->assertSame('Juan Perez', $this->twigExtension->shortName('Juan José Perez Pillo'));

        $this->assertSame('Juan', $this->twigExtension->shortName('Juan'));
    }
}
