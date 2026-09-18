<?php

use Rector\CodingStyle\Rector\FuncCall\FunctionFirstClassCallableRector;
use Rector\Config\RectorConfig;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector;
use Rector\Php71\Rector\List_\ListToArrayDestructRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\If_\IfToNullCoalescingAssignRector;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\Identical\StrEndsWithRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use RectorLaravel\Rector\Class_\ModelCastsPropertyToCastsMethodRector;
use RectorLaravel\Rector\ClassMethod\MigrateToSimplifiedAttributeRector;
use RectorLaravel\Rector\ClassMethod\ScopeNamedClassMethodToScopeAttributedClassMethodRector;
use RectorLaravel\Set\LaravelLevelSetList;

/**
 * Upgrade-only configuration: framework/package API renames and PHP 8.3
 * compatibility. Purely stylistic rewrites are skipped so the diff stays
 * reviewable and behaviour-neutral.
 */
return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/Modules',
        __DIR__ . '/config/function.php',
        __DIR__ . '/database',
        __DIR__ . '/routes',
    ])
    ->withSkip([
        __DIR__ . '/Modules/*/Resources/*',
        __DIR__ . '/Modules/*/Database/factories/*',
        __DIR__ . '/app/Http/Controllers/Bkp/*',
        // stylistic
        ClosureToArrowFunctionRector::class,
        LongArrayToShortArrayRector::class,
        TernaryToElvisRector::class,
        TernaryToNullCoalescingRector::class,
        IfToNullCoalescingAssignRector::class,
        StrContainsRector::class,
        StrStartsWithRector::class,
        StrEndsWithRector::class,
        ChangeSwitchToMatchRector::class,
        ListToArrayDestructRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
        RemoveUnusedVariableInCatchRector::class,
        StringClassNameToClassConstantRector::class,
        ReturnNeverTypeRector::class,
        ReadOnlyPropertyRector::class,
        AddOverrideAttributeToOverriddenMethodsRector::class,
        NullToStrictStringFuncCallArgRector::class,
        FunctionFirstClassCallableRector::class,
        // rewrites `$x == 'str'` to `$x == 0` to mimic PHP 7 loose comparison; never the intent here
        \Rector\Php80\Rector\NotIdentical\StrContainsRector::class,
        \Rector\Php80\Rector\Identical\StrEndsWithRector::class,
        \Rector\Php80\Rector\Identical\StrStartsWithRector::class,
        \Rector\Php80\Rector\Class_\StringableForToStringRector::class,
        \Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector::class,
        // Laravel style, not compatibility
        ModelCastsPropertyToCastsMethodRector::class,
        MigrateToSimplifiedAttributeRector::class,
        ScopeNamedClassMethodToScopeAttributedClassMethodRector::class,
    ])
    ->withPhpSets(php83: true)
    ->withSets([
        LaravelLevelSetList::UP_TO_LARAVEL_130_WITHOUT_ATTRIBUTES,
    ]);
