<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Entity\User;
use App\Service\TextFormatter;
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
        self::assertSame(
            'Juan Perez',
            $this->twigExtension->shortName('Juan Perez')
        );

        self::assertSame(
            'Juan Perez',
            $this->twigExtension->shortName('Juan José Perez')
        );

        self::assertSame(
            'Juan Perez',
            $this->twigExtension->shortName('Juan José Perez Pillo')
        );

        self::assertSame(
            'Juan',
            $this->twigExtension->shortName('Juan')
        );
    }
}
