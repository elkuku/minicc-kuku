<?php

namespace App\Tests\Controller;

use DirectoryIterator;
use Elkuku\SymfonyUtils\Test\ControllerBaseTest;
use Exception;
use Symfony\Bundle\FrameworkBundle\Routing\DelegatingLoader;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Routing\Route;

/**
 * Controller "smoke" test
 */
class ControllerAccessTest extends ControllerBaseTest
{
    protected string $controllerRoot = __DIR__.'/../../src/Controller';

    /**
     * @var array<int, string>
     */
    protected array $ignoredFiles
        = [
            '.gitignore',
            'GoogleController.php',
        ];

    /**
     * @var array<string, array<string, array<string, int|string>>>
     */
    protected array $exceptions
        = [
            'welcome'               => [
                'statusCodes' => ['GET' => 200],
            ],
            'about'                 => [
                'statusCodes' => ['GET' => 200],
            ],
            'contact'               => [
                'statusCodes' => ['GET' => 200],
            ],
            'login'                 => [
                'statusCodes' => ['GET' => 200, 'POST' => 302],
            ],
            'store-transaction-pdf' => [
                'params' => ['{year}' => '2000'],
            ],
        ];

    public function testAllRoutesAreProtected(): void
    {
        $this->runTests(static::createClient());
    }
}
