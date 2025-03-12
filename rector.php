<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Configuration\RectorConfigBuilder;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SymfonySetList;


return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withSkip([__DIR__ . '/src/Service/PhpXlsxGenerator.php',])
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
#        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
#        privatization: true,
#        naming: true
)
    ;



return static function (RectorConfig $rectorConfig): void {

    $rectorConfig->symfonyContainerXml(
        __DIR__.'/var/cache/dev/App_KernelDevDebugContainer.xml'
    );

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,

        SymfonySetList::SYMFONY_64,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,

        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
    ]);
};
