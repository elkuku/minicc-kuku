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
    ->withAttributesSets(
        symfony: true
    )
    ->withPreparedSets(
        deadCode: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        rectorPreset: true,
        phpunitCodeQuality: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
)
    ->withComposerBased(twig: true, doctrine: true, phpunit: true, symfony: true)
    ->withImportNames(removeUnusedImports: true)

;
