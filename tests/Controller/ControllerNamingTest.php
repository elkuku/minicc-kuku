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
        $routeLoader = static::bootKernel()->getContainer()
            ->get('routing.loader');

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->controllerRoot)
        );

        $it->rewind();
        while ($it->valid()) {
            if (!$it->isDot()
                && !in_array($it->getSubPathName(), $this->ignoredFiles, true)
            ) {
                $sub = $it->getSubPath() ? $it->getSubPath() . '\\' : '';

                $className = basename((string) $it->key(), '.php');

                $routerClass = 'App\Controller\\' . $sub . $className;
                $routes = $routeLoader->load($routerClass)->all();
                # var_dump($routes);
                if (count($routes) > 1) {
                    echo sprintf("Too many routes in %s (%d).\n", $routerClass, count($routes));
                    ++$failures;
                } else {
                    foreach ($routes as $name => $route) {
                        $expected = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $it->getSubPath() . $className));
                        if ($name !== $expected) {
                            echo sprintf("Wrong name for '%s' should be '%s'.\n", $routerClass, $expected);
                            ++$failures;
                        } else {
                            #echo 'OK: ' . $routerClass . "\n";
                        }
                    }
                }
            }

            $it->next();
        }

        self::assertEquals(0, $failures, sprintf("ERROR: %d failures.\n", $failures));
    }
}
