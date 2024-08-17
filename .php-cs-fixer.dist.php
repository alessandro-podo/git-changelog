<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in(__DIR__)
    ->name('*.php')
    ->ignoreDotFiles(true)
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP80Migration:risky' => true,
        '@PHP81Migration' => true,
        '@DoctrineAnnotation' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        'mb_str_functions' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'single_line_comment_style' => false,
        'phpdoc_to_comment' => false,
        'strict_comparison' => true,
        'global_namespace_import' => true,
        'php_unit_test_class_requires_covers' => false,
        'php_unit_internal_class' => false,
        'php_unit_strict' => false
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ;
