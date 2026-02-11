<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Helper\BreadcrumbTrait;
use PHPUnit\Framework\TestCase;

final class BreadcrumbTraitTest extends TestCase
{
    public function testGetBreadcrumbsReturnsDefaultHome(): void
    {
        $object = new class {
            use BreadcrumbTrait;

            /**
             * @return array<string, string>
             */
            public function exposeBreadcrumbs(): array
            {
                return $this->getBreadcrumbs();
            }
        };

        $breadcrumbs = $object->exposeBreadcrumbs();

        self::assertArrayHasKey('Home', $breadcrumbs);
        self::assertSame('welcome', $breadcrumbs['Home']);
    }

    public function testAddBreadcrumbAppendsToBreadcrumbs(): void
    {
        $object = new class {
            use BreadcrumbTrait;

            public function addCrumb(string $text, string $link = ''): self
            {
                return $this->addBreadcrumb($text, $link);
            }

            /**
             * @return array<string, string>
             */
            public function exposeBreadcrumbs(): array
            {
                return $this->getBreadcrumbs();
            }
        };

        $result = $object->addCrumb('Stores', '/stores');

        self::assertSame($object, $result);

        $breadcrumbs = $object->exposeBreadcrumbs();

        self::assertArrayHasKey('Home', $breadcrumbs);
        self::assertArrayHasKey('Stores', $breadcrumbs);
        self::assertSame('/stores', $breadcrumbs['Stores']);
    }

    public function testAddBreadcrumbWithEmptyLink(): void
    {
        $object = new class {
            use BreadcrumbTrait;

            public function addCrumb(string $text, string $link = ''): self
            {
                return $this->addBreadcrumb($text, $link);
            }

            /**
             * @return array<string, string>
             */
            public function exposeBreadcrumbs(): array
            {
                return $this->getBreadcrumbs();
            }
        };

        $object->addCrumb('Current Page');

        $breadcrumbs = $object->exposeBreadcrumbs();

        self::assertSame('', $breadcrumbs['Current Page']);
    }

    public function testMultipleBreadcrumbs(): void
    {
        $object = new class {
            use BreadcrumbTrait;

            public function addCrumb(string $text, string $link = ''): self
            {
                return $this->addBreadcrumb($text, $link);
            }

            /**
             * @return array<string, string>
             */
            public function exposeBreadcrumbs(): array
            {
                return $this->getBreadcrumbs();
            }
        };

        $object->addCrumb('Stores', '/stores');
        $object->addCrumb('Store 1', '/stores/1');
        $object->addCrumb('Edit');

        $breadcrumbs = $object->exposeBreadcrumbs();

        self::assertCount(4, $breadcrumbs);
        self::assertSame(['Home', 'Stores', 'Store 1', 'Edit'], array_keys($breadcrumbs));
    }
}
