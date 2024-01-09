<?php

$finder = PhpCsFixer\Finder::create()
    ->in('src/')
    ->in('tests/')
;

$config = new PhpCsFixer\Config();

return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => array('syntax' => 'short'),
        'combine_consecutive_unsets' => true,
        'heredoc_to_nowdoc' => true,
        'no_extra_blank_lines' => ['tokens' => ['break', 'continue', 'extra', 'return', 'throw', 'use', 'parenthesis_brace_block', 'square_brace_block', 'curly_brace_block']],
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
	'no_useless_return' => true,
        'non_printable_character' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'phpdoc_add_missing_param_annotation' => true,
	'phpdoc_order' => true,
        'psr_autoloading' => true,
        'strict_param' => true,
        'phpdoc_no_empty_return' => false,
        'ternary_to_elvis_operator' => false,
        'native_function_invocation' => ['include' => ['@compiler_optimized']],
    ])
    ->setFinder(
        $finder
    )
;
