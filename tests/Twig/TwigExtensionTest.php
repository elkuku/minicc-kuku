<?php

namespace Twig;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Twig\AppExtension;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TwigExtensionTest extends WebTestCase
{
    private AppExtension $twigExtension;

    public function setUp(): void
    {
        static::createClient();
        $this->twigExtension = new AppExtension(
            self::getContainer(),
            $this->createMock(UserRepository::class)
        );
    }

    public function testPriceFilter(): void
    {
        self::assertSame(
            '<span class="amount">112.00</span>',
            $this->twigExtension->priceFilter(112)
        );
        self::assertSame(
            '<span class="amount amount-red">-112.00</span>',
            $this->twigExtension->priceFilter(-112)
        );
    }

    public function testX(): void
    {
        self::assertSame(112.0, $this->twigExtension->conIvaFilter(100));
        self::assertSame(12.0, $this->twigExtension->taxFromTotalFilter(112));
    }

    public function testFormatRuc(): void
    {
        $user = (new User())
            ->setInqCi('123456789-6');

        self::assertSame(
            '123 456 789 6',
            $this->twigExtension->formatRUC($user)
        );

        $user = (new User())
            ->setInqRuc('1234567896001');

        self::assertSame(
            '123 456 789 6 001',
            $this->twigExtension->formatRUC($user)
        );

        $user = (new User())
            ->setInqRuc('12345678961');

        self::assertSame(
            '123 456 789 61',
            $this->twigExtension->formatRUC($user)
        );
    }

    public function testIntlDate(): void
    {
        self::assertSame(
            '15 de septiembre 1966',
            $this->twigExtension->intlDate('1966-09-15')
        );

        self::assertSame(
            '15 de September 1966',
            $this->twigExtension->intlDate('1966-09-15', lang: 'de_DE')
        );

        self::assertSame(
            '15 September 1966',
            $this->twigExtension->intlDate('1966-09-15', 'd MMMM YYYY', 'de_DE')
        );

        self::assertSame(
            'INVALID',
            $this->twigExtension->intlDate('INVALID')
        );
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
