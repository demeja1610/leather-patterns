<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorLaravel\Set\LaravelSetList;
use RectorLaravel\Set\LaravelLevelSetList;
use Rector\Transform\Rector\String_\StringToClassConstantRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/lang',
        __DIR__ . '/routes',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets()
    ->withPreparedSets(
        typeDeclarations: true,
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        // naming: true, // disabled because it renaming methods argument names & variable names
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true,
        // carbon: true, // disabled because it enforces Carbon\Carbon usage in DateTime related methods
        rectorPreset: true,
        phpunitCodeQuality: true,
        doctrineCodeQuality: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withSets([
        LaravelLevelSetList::UP_TO_LARAVEL_120,
        LaravelSetList::LARAVEL_ARRAYACCESS_TO_METHOD_CALL,
        LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelSetList::LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME,
        LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,
        LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,
        // LaravelSetList::LARAVEL_IF_HELPERS, // disabled because it enforces to use, for example, abort_if instead of abort in if statment
        LaravelSetList::LARAVEL_LEGACY_FACTORIES_TO_CLASSES,

        // // disabled because it enforces to use DI in classes that doesnt support it (migrations)
        // // enable it when this rule will be fixed ot when we can exclude migrations (or other files) from this rule
        // LaravelSetList::LARAVEL_STATIC_TO_INJECTION,
    ])
    ->withSkip([
        PostIncDecToPreIncDecRector::class, // skip this rule because it inforces to use ++$i instead of $i++
        EncapsedStringsToSprintfRector::class, // skip this rule because it inforces to use sprintf instead of concatenation
        LaravelSetList::LARAVEL_STATIC_TO_INJECTION => [
            __DIR__ . '/database/migrations/*',
        ],
        StringToClassConstantRector::class => [
            __DIR__ . '/app/Enum/RouteNamesEnum.php',
        ],
    ])
    ->withSkipPath(__DIR__ . '/app/Console/Commands/*')
    ->withSkipPath(__DIR__ . '/bootstrap/cache/*')
;
