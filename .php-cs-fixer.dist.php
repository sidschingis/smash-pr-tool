<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'global_namespace_import' => [
            'import_classes' => true,
        ],
        'no_unused_imports' => true,
    ])
    ->setFinder($finder)
;
