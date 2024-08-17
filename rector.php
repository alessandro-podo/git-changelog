<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector;
use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;
use Rector\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Set\TwigSetList;

return RectorConfig::configure()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: false, # zerhaut setup bei den unit tests
        naming: false, # da man keine Ausnahmen machen kann ist es nicht vorteilhaft
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true,
    )
    ->withAttributesSets(symfony: true, doctrine: true)
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withRootFiles()
    ->withPhpSets()
    ->withImportNames()
    ->withSkip([
        # verhindert das Decorator
        ReadOnlyPropertyRector::class,

        # macht den Code unlessbarer (SetList::EARLY_RETURN)
        ChangeOrIfContinueToMultiContinueRector::class,
        ReturnBinaryOrToEarlyReturnRector::class,

        # macht probleme bei deploy.php
        AbsolutizeRequireAndIncludePathRector::class,
    ])
    ->withSets([
        # Doctrine
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        DoctrineSetList::GEDMO_ANNOTATIONS_TO_ATTRIBUTES,
        DoctrineSetList::DOCTRINE_CODE_QUALITY,

        # Framework
        TwigSetList::TWIG_240,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
        SymfonySetList::SYMFONY_64,
        SymfonySetList::CONFIGS,

        # SetList
        SetList::TYPE_DECLARATION,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
    ])
    ->withoutParallel()
;
