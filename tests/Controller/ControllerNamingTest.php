<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\Routing\DelegatingLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ControllerNamingTest extends KernelTestCase
{
    private string $controllerRoot = __DIR__ . '/../../src/Controller';

    /**
     * @var array<int, string>
     */
    private array $ignoredFiles = [
        '.gitignore',
        'GoogleController.php',
        'Security/GoogleIdentityController.php',
        'Security/SecurityController.php',
    ];

    public function testNaming(): void
    {
        $failures = 0;

        /**
         * @var DelegatingLoader $routeLoader
         */
        $routeLoader = self::bootKernel()->getContainer()
            ->get('routing.loader');

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->controllerRoot)
        );

        $it->rewind();
        while ($it->valid()) {
            if (!$it->isDot() && !in_array($it->getSubPathName(), $this->ignoredFiles, true)) {
                $failures += $this->checkController($it, $routeLoader);
            }

            $it->next();
        }

        $this->assertSame(0, $failures, sprintf("ERROR: %d failures.\n", $failures));
    }

    /**
     * @param RecursiveIteratorIterator<RecursiveDirectoryIterator> $it
     */
    private function checkController(RecursiveIteratorIterator $it, DelegatingLoader $routeLoader): int
    {
        $sub = $it->getSubPath() !== '' && $it->getSubPath() !== '0' ? $it->getSubPath().'\\' : '';

        $key = $it->key();
        self::assertIsString($key);
        $className = basename($key, '.php');
        $routerClass = 'App\Controller\\'.$sub.$className;
        $routes = $routeLoader->load($routerClass)->all();

        if (count($routes) > 1) {
            echo sprintf("Too many routes in %s (%d).\n", $routerClass, count($routes));

            return 1;
        }

        return $this->checkRouteNaming($routes, $it->getSubPath(), $className, $routerClass);
    }

    /**
     * @param array<string, mixed> $routes
     */
    private function checkRouteNaming(array $routes, string $subPath, string $className, string $routerClass): int
    {
        foreach ($routes as $name => $route) {
            $expected = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $subPath.$className));
            if ($name !== $expected) {
                echo sprintf("Wrong name for '%s' should be '%s'.\n", $routerClass, $expected);

                return 1;
            }
        }

        return 0;
    }
}
