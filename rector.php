<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\CodeQuality\Rector\FuncCall\SortCallLikeNamedArgsRector;
use Rector\CodeQuality\Rector\If_\ExplicitNullableObjectInstanceRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Rector\DeadCode\Rector\FuncCall\RemoveFilterVarOnExactTypeRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector;
use RectorLaravel\Rector\ClassMethod\RelationTableStringToPivotClassRector;
use RectorLaravel\Rector\Coalesce\ApplyDefaultInsteadOfNullCoalesceRector;
use RectorLaravel\Rector\FuncCall\AppToResolveRector;
use RectorLaravel\Rector\FuncCall\ThrowIfAndThrowUnlessExceptionsToUseClassStringRector;
use RectorLaravel\Rector\If_\ThrowIfRector;
use RectorLaravel\Rector\MethodCall\EloquentOrderByToLatestOrOldestRector;
use RectorLaravel\Rector\MethodCall\EloquentWhereRelationTypeHintingParameterRector;
use RectorLaravel\Rector\MethodCall\EloquentWhereTypeHintClosureParameterRector;
use RectorLaravel\Rector\MethodCall\ValidationRuleArrayStringValueToArrayRector;
use RectorLaravel\Rector\StaticCall\CarbonToDateFacadeRector;
use RectorLaravel\Set\LaravelLevelSetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap/app.php',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withCache(__DIR__.'/storage/framework/cache/rector')
    ->withParallel(timeoutSeconds: 300)
    ->withPhpSets(php83: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
    )
    ->withSets([
        LaravelLevelSetList::UP_TO_LARAVEL_130_WITHOUT_ATTRIBUTES,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,
        LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,
        LaravelSetList::LARAVEL_IF_HELPERS,
        LaravelSetList::LARAVEL_TESTING,
        LaravelSetList::LARAVEL_TYPE_DECLARATIONS,
    ])
    ->withImportNames(importShortClasses: false, removeUnusedImports: true)
    ->withSkip([
        // Behaviour changes: strict types, === instead of == ('1e3' == '1000'), match instead of switch.
        SafeDeclareStrictTypesRector::class,
        UseIdenticalOverEqualWithSameTypeRector::class,
        ChangeSwitchToMatchRector::class,

        // Trust PHPDoc types (@property tags, inferred column types) more than the code does;
        // casts and checks stay until tests cover them.
        RecastingRemovalRector::class,
        RemoveFilterVarOnExactTypeRector::class,
        SimplifyEmptyCheckOnEmptyArrayRector::class,

        // `?? $default` and `input($key, $default)` differ when the key holds null.
        ApplyDefaultInsteadOfNullCoalesceRector::class,

        // Swaps the pivot of belongsToMany() for a pivot model, with its casts and events.
        RelationTableStringToPivotClassRector::class,

        // throw_if() builds the exception inside the helper (its file and line point there)
        // and PHPStan loses the narrowing an if/throw gives, e.g. preg_match() matches.
        ThrowIfRector::class,
        ThrowIfAndThrowUnlessExceptionsToUseClassStringRector::class,

        // `=== null` already narrows a ?Type; a negated instanceof reads worse and adds imports.
        ExplicitNullableObjectInstanceRector::class,

        // oldest()/latest() suit timestamps, not expiry or visit dates next to a second
        // orderBy(); the rewrite also folds multi-line query chains onto one line.
        EloquentOrderByToLatestOrOldestRector::class,

        // They type closure parameters as the empty Contracts\Database\Query\Builder, which
        // loses the model (scopes, generics) of the Eloquent builder the closure receives.
        EloquentWhereTypeHintClosureParameterRector::class,
        EloquentWhereRelationTypeHintingParameterRector::class,

        // Class names stored in old rows: the migration must not follow a later class rename.
        StringClassNameToClassConstantRector::class => [
            __DIR__.'/database/migrations/*',
            __DIR__.'/tests/Feature/Database/MorphMapTest.php',
        ],

        // Named arguments stay in the order that reads best (bootstrap/app.php, the menu);
        // sorting them to the parameter order also changes their evaluation order.
        SortCallLikeNamedArgsRector::class,

        // Style churn against Laravel idioms (compact() views, pipe rules, app(), now()).
        CompactToVariablesRector::class,
        ValidationRuleArrayStringValueToArrayRector::class,
        AppToResolveRector::class,
        CarbonToDateFacadeRector::class,
    ]);
