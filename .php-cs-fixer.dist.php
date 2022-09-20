<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@DoctrineAnnotation' => true,
        'single_quote' => ['strings_containing_single_quote_chars' => true],
    ])
    ->setFinder($finder)
;
