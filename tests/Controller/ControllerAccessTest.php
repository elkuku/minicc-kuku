<?php

namespace App\Tests\Controller;

use DirectoryIterator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

/**
 * Controller "smoke" test
 */
class ControllerAccessTest extends WebTestCase
{
    private array $exceptions
        = [
            'welcome' => [
                'statusCode' => 200,
            ],
            'about'   => [
                'statusCode' => 200,
            ],
            'contact' => [
                'statusCode' => 200,
            ],
            'login'   => [
                'statusCode' => 200,
            ],
        ];

    /**
     * @throws Exception
     */
    public function testRoutes(): void
    {
        $client = static::createClient();

        $routeLoader = static::bootKernel()->getContainer()
            ->get('routing.loader');

        foreach (
            new DirectoryIterator(__DIR__.'/../../src/Controller') as $item
        ) {
            if (
                $item->isDot()
                || $item->isDir()
                || in_array(
                    $item->getBasename(),
                    ['.gitignore', 'GoogleController.php']
                )
            ) {
                continue;
            }

            $routerClass = 'App\Controller\\'.basename(
                    $item->getBasename(),
                    '.php'
                );
            $routes = $routeLoader->load($routerClass)->all();

            $this->processRoutes($routes, $client);
        }
    }

    private function processRoutes(array $routes, AbstractBrowser $browser): void
    {
        foreach ($routes as $routeName => $route) {
            $defaultId = 1;
            $expectedStatusCode = 302;
            if (array_key_exists($routeName, $this->exceptions)) {
                if (array_key_exists(
                    'statusCode',
                    $this->exceptions[$routeName]
                )
                ) {
                    $expectedStatusCode = $this->exceptions[$routeName]['statusCode'];
                }
                if (array_key_exists('params', $this->exceptions[$routeName])) {
                    $params = $this->exceptions[$routeName]['params'];
                    if (array_key_exists('id', $params)) {
                        $defaultId = $params['id'];
                    }
                }
            }

            $methods = $route->getMethods() ;

            if (!$methods) {
                echo sprintf('No methods set in controller "%s"',$route->getPath()).PHP_EOL;
                $methods = ['GET'];
            }

            // WTF start
            if (isset($methods[0]) && strpos($methods[0], '|')) {
                // The first element contains a "|" character - WTF??
                $methods = explode('|', $methods[0]);
            }
            // WTF end

            $path = str_replace('{id}', $defaultId, $route->getPath());
            $out = false;
            foreach ($methods as $method) {
                if ($out) {
                    echo sprintf(
                            'Testing: %s - %s Expected: %s ... ',
                            $method,
                            $path,
                            $expectedStatusCode,
                        );
                }

                $browser->request($method, $path);

                if ($out) {
                    echo sprintf(
                            ' got: %s',
                            $browser->getResponse()->getStatusCode()
                        ).PHP_EOL;
                }

                self::assertEquals(
                    $expectedStatusCode,
                    $browser->getResponse()->getStatusCode(),
                    sprintf('failed: %s (%s)', $routeName, $path)
                );
            }
        }
    }
}
