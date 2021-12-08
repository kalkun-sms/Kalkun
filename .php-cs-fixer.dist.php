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
        // 0-spaces
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
        'no_blank_lines_after_class_opening' => true,

        # 1
        'single_quote' => true,

        # 2


        # 3
        'constant_case' => [ 'case' => 'upper'], //TRUE, FALSE...

        # 4
        'braces' => [ 'position_after_control_structures' => 'next'],
        'control_structure_continuation_position' => [ 'position' => 'next_line'],

        # 5
        //'strict_comparison' => true, // Remplace == by === etc... --> RISKY

        # 6
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],

        # 7
        'explicit_string_variable' => true,

        # 8
        'no_closing_tag' => true,

        # 9
        'linebreak_after_opening_tag' => true,

        # 10
        'single_line_comment_style' => true,

        # 11
        'not_operator_with_space' => true,
        'no_spaces_inside_parenthesis' => true, # Use with not_operator_with_space to be sure "( ! $var)" remains correct.
        'object_operator_without_whitespace' => true,
        'operator_linebreak' => [ 'only_booleans' => true ],
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
        'unary_operator_spaces' => true,
        'binary_operator_spaces' => true,

        # 12
        // 'no_alternative_syntax' => true, //Don't use it here because we want to keep alternative syntax on views

    ])
    ->setFinder($finder)

;
