<?php

namespace Controller;

use DirectoryIterator;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Controller "smoke" test
 */
class ControllerAccessTest extends WebTestCase
{
    private $routeLoader;

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

    private function processRoutes(array $routes, KernelBrowser $browser): void
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

            $methods = $route->getMethods() ?: ['GET'];

            // WTF start
            if (isset($methods[0]) && strpos($methods[0], '|')) {
                // The first element contains a "|" character - WTF??
                $methods = explode('|', $methods[0]);
            }
            // WTF end

            $path = str_replace('{id}', $defaultId, $route->getPath());
            foreach ($methods as $method) {
                $browser->request($method, $path);
                if (true) {
                    echo sprintf(
                            'Testing: %s - %s Expected: %s got: %s',
                            $method,
                            $path,
                            $expectedStatusCode,
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

    // protected KernelBrowser $client;
    //
    // protected function setUp(): void
    // {
    //     $this->client = static::createClient();
    //     // parent::setUp();
    //     // $kernel = static::bootKernel();
    //
    //     // $this->addFixture(new StoreFixture());
    //     // $this->addFixture(new TransactionFixture());
    //     // $this->addFixture(new PaymentMethodFixture());
    //     // $this->addFixture(new ContractFixture());
    //     // $this->addFixture(new UserFixture());
    //     //
    //     // $this->executeFixtures();
    //
    //     // $this->routeLoader = $kernel->getContainer()->get('routing.loader');
    //     $this->routeLoader = static::bootKernel()->getContainer()
    //         ->get('routing.loader');
    // }
    //
    // /**
    //  * @throws Exception
    //  */
    // private function loadRoutes($controllerName)
    // {
    //     $routerClass = 'App\Controller\\'.$controllerName;
    //
    //     if (class_exists($routerClass)) {
    //         return $this->routeLoader->load($routerClass);
    //     }
    //
    //     return false;
    // }
    //
    // /**
    //  * @throws Exception
    //  */
    // public function testShowPage(): void
    // {
    //     $path = __DIR__.'/../../src/Controller';
    //
    //     foreach (new DirectoryIterator($path) as $item) {
    //         if (
    //             $item->isDot()
    //             || in_array(
    //                 $item->getBasename(),
    //                 ['.gitignore', 'GoogleController.php']
    //             )
    //         ) {
    //             continue;
    //         }
    //
    //         if ('TransactionController.php' === $item->getBasename()) {
    //             // @todo transactions is not a valid table name in SQLite :(
    //             continue;
    //         }
    //
    //         $controllerName = basename($item->getBasename(), '.php');
    //
    //         $r = $this->loadRoutes($controllerName);
    //
    //         if (!$r) {
    //             continue;
    //         }
    //
    //         $routes = $r->all();
    //
    //         foreach ($routes as $routeName => $route) {
    //             $method = 'GET';
    //             $defaultId = 1;
    //             $defaultExpected = 302;
    //
    //             if (array_key_exists($routeName, $this->exceptions)) {
    //                 if (array_key_exists(
    //                     'method',
    //                     $this->exceptions[$routeName]
    //                 )
    //                 ) {
    //                     $method = $this->exceptions[$routeName]['method'];
    //                 }
    //                 if (array_key_exists(
    //                     'expected',
    //                     $this->exceptions[$routeName]
    //                 )
    //                 ) {
    //                     $defaultExpected = $this->exceptions[$routeName]['expected'];
    //                 }
    //                 if (array_key_exists(
    //                     'params',
    //                     $this->exceptions[$routeName]
    //                 )
    //                 ) {
    //                     $params = $this->exceptions[$routeName]['params'];
    //                     if (array_key_exists('id', $params)) {
    //                         $defaultId = $params['id'];
    //                     }
    //                 }
    //             }
    //
    //             $path = $route->getPath();
    //             $path = str_replace('{id}', $defaultId, $path);
    //             // echo 'Testing: '.$path.PHP_EOL;
    //             $this->client->request($method, $path);
    //             self::assertEquals(
    //                 $defaultExpected,
    //                 $this->client->getResponse()->getStatusCode(),
    //                 sprintf('failed: %s (%s)', $routeName, $path)
    //             );
    //         }
    //     }
    // }
}
