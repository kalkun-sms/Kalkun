<?php

    /**
     * Sample command line bot for sending a message
     * Modified from: app/sendMessage.php
     * Usage: cd /path/to/jaxl
     * 	      Edit username/password below
     * 		  Run from command line: /path/to/php sendMessage.php "username@gmail.com" "Your message"
     * 		  View jaxl.log for detail
     * 
     * Read More: http://jaxl.net/examples/sendMessage.php
    */

	// Initialize Jaxl Library
    require_once 'core/jaxl.class.php';
	
    // Values passed to the constructor can also be defined as constants
    // List of constants can be found inside "../../env/jaxl.ini"
    // Note: Values passed to the constructor always overwrite defined constants
    $jaxl = new JAXL(array(
        'user'=>$argv[1],
        'pass'=>$argv[2],
        'host'=>$argv[3],
        'domain'=>$argv[4],
        'authType'=>'PLAIN',
        'logLevel'=>5
    ));

    // Post successful auth send desired message
    function postAuth($payload, $jaxl) {
        global $argv;
        
        $msg = $argv;
        unset($msg[0], $msg[1], $msg[2], $msg[3], $msg[4], $msg[5]);
        $message = implode(' ', $msg);
        
        $jaxl->sendMessage($argv[5], $message);
        $jaxl->shutdown();
    }

    // Register callback on required hooks
    $jaxl->addPlugin('jaxl_post_auth', 'postAuth');

    // Fire start Jaxl core
    $jaxl->startCore("stream");

?>
