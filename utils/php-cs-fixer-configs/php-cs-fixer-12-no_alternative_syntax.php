<?php

# Get $finder variable
include_once 'finder.inc.php';

// Finder additions for this specific config
$finder->exclude('views'); // Don't apply it on views for now

$config = new PhpCsFixer\Config();
return $config
    ->setIndent("\t") // As per CI3 coding style
    ->setLineEnding("\n") // As per CI3 coding style
    ->setRules([
        'no_alternative_syntax' => true,

        // Spaces
        'encoding' => true,
        'indentation_type' => true,
        'line_ending' => true,
        'no_trailing_whitespace' => true,
        'no_whitespace_in_blank_line' => true,
        'single_blank_line_at_eof' => true,
        'no_trailing_whitespace_in_comment' => true,

        'array_indentation' => true,
        'no_whitespace_before_comma_in_array' => true,
        'whitespace_after_comma_in_array' => true,
        'trim_array_spaces' => true,
        'no_spaces_around_offset' => true,
        'no_blank_lines_after_class_opening' => true
    ])
    ->setFinder($finder)

;
