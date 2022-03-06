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
    ->notPath('views') // Don't apply it on views for now
    ->in('application')
    //->in(__DIR__)
;

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
