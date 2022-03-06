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
        'strict_comparison' => true, // Remplace == by === etc... --> RISKY

        // Don't change anything else here (like space fixing) because we use
        // this config only to detect strict_comparison errors

    ])
    ->setFinder($finder)

;
