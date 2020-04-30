<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
|
*/
    //$config['optout_keywords'] = array('STOP', 'STOPALL', 'UNSUBSCRIBE', 'END', 'QUIT', 'CANCEL');
    $config['optout_keywords'] = array('STOP');
    //$config['optin_keywords'] = array('ACTIVER', 'START', 'YES', 'UNSTOP');
    $config['optin_keywords'] = array('ACTIVER');
    $config['type_keywords'] = array('rappel', 'annul');

    // Send autoreply to confirm action is saved
    $config['enable_autoreply_info'] = TRUE;
    // Send autoreply to tell command is invalid
    $config['enable_autoreply_error'] = FALSE;
    // Enable opt-in
    $config['enable_optin'] = TRUE;
    // Enable the use of "type" in optin/optout
    $config['enable_type'] = FALSE;

    $config['autoreply_language'] = 'english';
    $config['enable_autoreply_outnumber_filter'] = FALSE;
    // Send only if this is a french mobile phone number ( +336 et +337 )
    $config['autoreply_outnumber_match_rule'] = '/\+33[67][0-9]{8}/';

/* End of file kalkun_plugin_stop_manager.php */
/* Location: ./application/plugins/stop_manager/config/stop_manager.php */
