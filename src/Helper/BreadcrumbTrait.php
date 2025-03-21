<?php

declare(strict_types=1);

namespace App\Helper;

trait BreadcrumbTrait
{
    /**
     * @var array<string, string>
     */
    private array $breadcrumbs = [];

    protected function addBreadcrumb(string $text, string $link = ''): self
    {
        $this->initBreadcrumbs();

        $this->breadcrumbs[$text] = $link;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    protected function getBreadcrumbs(): array
    {
        return $this->initBreadcrumbs()->breadcrumbs;
    }

    private function initBreadcrumbs(): self
    {
        if (!$this->breadcrumbs) {
            $this->breadcrumbs = [
                'Home' => 'welcome',
            ];
        }

        return $this;
    }
}
