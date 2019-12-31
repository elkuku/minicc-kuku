<?php

namespace App\Tests\Controller;

use App\Tests\FixtureAwareTestCase;
use App\Tests\Fixtures\ContractFixture;
use App\Tests\Fixtures\PaymentMethodFixture;
use App\Tests\Fixtures\StoreFixture;
use App\Tests\Fixtures\TransactionFixture;
use App\Tests\Fixtures\UserFixture;

class ControllerAccessTest extends FixtureAwareTestCase
{
    private $routeLoader;

    private $exceptions
        = [
            'welcome'               => [
                'expected' => 200,
            ],
            'about'               => [
                'expected' => 200,
            ],
            'contact'               => [
                'expected' => 200,
            ],


            'login'             => [
                'expected' => 200,
            ],

            'agent_add_comment'     => [
                'method' => 'POST',
            ],
            'agent_lookup'          => [
                'method' => 'POST',
            ],
            'comment_delete_inline' => [
                'method' => 'DELETE',
            ],
        ];

    protected $client;

    protected function setUp()
    {
        $this->client = static::createClient();
        parent::setUp();
        $kernel = static::bootKernel();

        $this->addFixture(new StoreFixture());
        $this->addFixture(new TransactionFixture());
        $this->addFixture(new PaymentMethodFixture());
        $this->addFixture(new ContractFixture());
        $this->addFixture(new UserFixture());

        $this->executeFixtures();

        $this->routeLoader = $kernel->getContainer()->get('routing.loader');
    }

    private function loadRoutes($controllerName)
    {
        $routerClass = 'App\Controller\\'.$controllerName;

        return $this->routeLoader->load($routerClass);
    }

    public function testShowPage()
    {
        $path = __DIR__.'/../../src/Controller';

        foreach (new \DirectoryIterator($path) as $item) {
            if (
                $item->isDot()
                || in_array(
                    $item->getBasename(), ['.gitignore', 'GoogleController.php']
                )
            ) {
                continue;
            }

            $controllerName = basename($item->getBasename(), '.php');

            $routes = $this->loadRoutes($controllerName)->all();

            foreach ($routes as $routeName => $route) {
                $method = 'GET';
                $defaultId = 1;
                $defaultExpected = 302;

                if (array_key_exists($routeName, $this->exceptions)) {
                    if (array_key_exists('method', $this->exceptions[$routeName])) {
                        $method = $this->exceptions[$routeName]['method'];
                    }
                    if (array_key_exists('expected', $this->exceptions[$routeName])) {
                        $defaultExpected = $this->exceptions[$routeName]['expected'];
                    }
                    if (array_key_exists('params', $this->exceptions[$routeName])) {
                        $params = $this->exceptions[$routeName]['params'];
                        if (array_key_exists('id', $params)) {
                            $defaultId = $params['id'];
                        }
                    }
                }

                $path = $route->getPath();
                $path = str_replace('{id}', $defaultId, $path);
                 echo 'Testing: '.$path.PHP_EOL;
                $this->client->request($method, $path);
                $this->assertEquals(
                    $defaultExpected,
                    $this->client->getResponse()->getStatusCode(),
                    sprintf('failed: %s (%s)', $routeName, $path)
                );
            }
        }
    }
}
