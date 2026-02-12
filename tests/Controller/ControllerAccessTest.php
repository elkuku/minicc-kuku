<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Elkuku\SymfonyUtils\Test\ControllerBaseTest;

/**
 * Controller "smoke" test.
 */
final class ControllerAccessTest extends ControllerBaseTest
{
    protected string $controllerRoot = __DIR__ . '/../../src/Controller';

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
            'welcome' => [
                'statusCodes' => [
                    'GET' => 200,
                ],
            ],
            'login' => [
                'statusCodes' => [
                    'GET' => 200,
                ],
            ],
        ];

    public function testAllRoutesAreProtected(): void
    {
        $this->runTests(self::createClient());
    }
}
