<?php
declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Rector\Set\ValueObject\SetList;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;

return RectorConfig::configure()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        naming: true,
        privatization: true,
        typeDeclarations: true,
        rectorPreset: true,
    )
    ->withRules([
        // Añade tipado a las propiedades basadas en el constructor
        TypedPropertyFromStrictConstructorRector::class,
        SimplifyIfReturnBoolRector::class,
    ])
    ->withSets([
        // Solo usa las versiones necesarias de PHP
        SetList::PHP_74,
        SetList::PHP_80,
        SetList::PHP_81,
        SetList::PHP_82,
    ])
    ->withPaths([
        __DIR__ . '/src'
    ]);
?>