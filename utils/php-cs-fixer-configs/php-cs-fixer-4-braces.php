<?php

$finder = PhpCsFixer\Finder::create()
    //->notPath('file.php')
    ->exclude('libraries')
    ->exclude('plugins/jsonrpc/libraries/')
    ->exclude('plugins/rest_api/libraries/')
    ->exclude('plugins/sms_to_twitter/libraries/')
    ->exclude('plugins/sms_to_wordpress/')
    ->exclude('plugins/sms_to_xmpp/libraries/abhinavsingh-JAXL-5829c3b/')
    ->exclude('plugins/soap/libraries/')
    ->exclude('tests/mocks/libraries/')
    ->exclude('third_party/')
    ->notPath('config/autoload.php')
    ->notPath('config/config.php')
    ->notPath('config/constants.php')
    ->notPath('config/database.php')
    ->notPath('config/doctypes.php')
    ->notPath('config/foreign_chars.php')
    ->notPath('config/hooks.php')
    //->notPath('config/kalkun_settings.php')
    ->notPath('config/memcached.php')
    ->notPath('config/migration.php')
    ->notPath('config/mimes.php')
    //->notPath('config/plugins.php')
    ->notPath('config/profiler.php')
    //->notPath('config/routes.php')
    ->notPath('config/smileys.php')
    ->notPath('config/user_agents.php')
    ->in('application')
    //->in(__DIR__)
;


$config = new PhpCsFixer\Config();
return $config
    ->setIndent("\t") // As per CI3 coding style
    ->setLineEnding("\n") // As per CI3 coding style
    ->setRules([
        'braces' => [ 'position_after_control_structures' => 'next'],
        'control_structure_continuation_position' => [ 'position' => 'next_line'],

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
