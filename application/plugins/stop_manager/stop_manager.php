<?php
/**
* Plugin Name: Stop Manager
* Plugin URI: --
* Version: 1.0
* Description: Manage incoming SMS containing STOP
* Author: tenzap
* Author URI: https://github.com/tenzap
*/

// Add hook for outgoing message
add_action("message.outgoing_dest_data", "stop_manager_cleanup_outgoing", 1);
add_action("message.incoming.before", "stop_manager_incoming", 1);

function stop_manager_activate()
{
    return true;
}

/**
* Function called when plugin deactivated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
*
* Format: pluginname_deactivate
*
*/
function stop_manager_deactivate()
{
    return true;
}

/**
* Function called when plugin first installed into the database
* Utility function must be prefixed with the plugin name
* followed by an underscore.
*
* Format: pluginname_install
*
*/
function stop_manager_install()
{
    $CI =& get_instance();
    $CI->load->helper('kalkun');
    // check if table already exists
    if (!$CI->db->table_exists('plugin_stop_manager'))
    {
        $db_driver = $CI->db->platform();
        $db_prop = get_database_property($db_driver);
        execute_sql(APPPATH."plugins/stop_manager/media/".$db_prop['file']."_stop_manager.sql");
    }
  return true;
}

/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
|
*/
function stop_manager_initialize()
{
    $CI =& get_instance();
    $CI->config->load("kalkun_plugin_stop_manager", TRUE);

    $config['optout_keywords'] = $CI->config->item('optout_keywords', 'kalkun_plugin_stop_manager');
    $config['optin_keywords'] = $CI->config->item('optin_keywords', 'kalkun_plugin_stop_manager');
    $config['type_keywords'] = $CI->config->item('type_keywords', 'kalkun_plugin_stop_manager');

    $config['enable_autoreply_info'] = $CI->config->item('enable_autoreply_info', 'kalkun_plugin_stop_manager');
    $config['enable_autoreply_error'] = $CI->config->item('enable_autoreply_error', 'kalkun_plugin_stop_manager');
    $config['enable_optin'] = $CI->config->item('enable_optin', 'kalkun_plugin_stop_manager');
    $config['enable_type'] = $CI->config->item('enable_type', 'kalkun_plugin_stop_manager');

    $config['enable_autoreply_outnumber_filter'] = $CI->config->item('enable_autoreply_outnumber_filter', 'kalkun_plugin_stop_manager');;
    $config['autoreply_outnumber_match_rule'] = $CI->config->item('autoreply_outnumber_match_rule', 'kalkun_plugin_stop_manager');

    return $config;
}

function stop_manager_cleanup_outgoing($all)
{
    $config = stop_manager_initialize();

    $dest=$all[0];
    $data=$all[1];

    $CI =& get_instance();

    // Get the type of the SMS (rappel, annul...)
    $msg = $data['message'];
    // Be careful! Kalkun may append $config['append_username_message'] to all messages.
    $ret_match = NULL;
    if($CI->config->item('append_username')) {
        $ret_match = preg_match('/^(.*)~(.+)~.*/', $msg, $matches, PREG_UNMATCHED_AS_NULL);
    } else {
        $ret_match = preg_match('/^(.*)~(.+)~$/', $msg, $matches, PREG_UNMATCHED_AS_NULL);
    }

    $type = NULL;
    if ($ret_match && isset($matches[2]) && $config['enable_type']) {
        $type = $matches[2];
    }
    if (is_null($type)) {
        // type of SMS (for filtering) is not set yet.
        // The message is sent    if we enabled  the use of type ($config['enable_type'])
        // The message is dropped if we disabled the use of type ($config['enable_type']) and if it is in blacklist
        if (!$config['enable_type']) {
            // Will drop all numbers that are in stop_manager whatever the value of type
            //$type = "%";

            // Will drop all numbers that are in stop_manager having been recorded as TYPE_NOT_SET_SO_STOP_ALL
            $type = "TYPE_NOT_SET_SO_STOP_ALL";
        } else {
            // IGNORE_STOP_MANAGER is just a fake value that should never match something in the table,
            // this is to keep the message
            $type = "IGNORE_STOP_MANAGER";
        }
    }

    // Récupérer la liste des numéros ayant STOP pour ce type de sms
    $CI->load->model('stop_manager/Stop_manager_model', 'Stop_manager_model');
    $db_result = $CI->Stop_manager_model->get_num_for_type($type)->result_array();
    $blocked_numbers = array();

    foreach ($db_result as $row) {
        $blocked_numbers[] = $row['destination_number'];
    }

    // Supprimer le n° de tel si le destinataire est dans la base des STOP pour ce type de sms_to_email
    foreach($dest as $key => $number) {
        foreach($blocked_numbers as $n) {
            if($n == $number) {
                unset($dest[$key]);
            }
        }
    }

    // Supprimer à l'intérieur des messages le "tag" qui permet de savoir de quel message il s'agit
    // Par ex ~rappel~ en fin de message
    if ($ret_match && isset($matches[1])) {
        $data['message'] = trim($matches[1]);
    }
    return array($dest, $data);
}

function stop_manager_incoming($sms)
{
    $config = stop_manager_initialize();

    $optout_keywords = array_map('strtoupper', $config['optout_keywords']);
    $optin_keywords = array_map('strtoupper', $config['optin_keywords']);
    $type_keywords = array_map('strtolower', $config['type_keywords']);

    // A la reception du message, si c'est un message STOP (STOP rappel) par exemple
    // Le mettre dans la table des STOP
    $msg = $sms->TextDecoded;
    $from = $sms->SenderNumber;
    //$msg_user = $sms->msg_user;

    $cmds_valides = array_merge($optout_keywords, $optin_keywords);
    $types_valides = $type_keywords;

    $types_reg = implode('|', $types_valides);
    $cmds_reg = implode('|', $cmds_valides);

    if ($config['enable_type'])
        $ret = preg_match('/\b('.$cmds_reg.')\s*('.$types_reg.')\b/i', $msg, $matches, PREG_UNMATCHED_AS_NULL);
    else
        $ret = preg_match('/\b('.$cmds_reg.')\b/i', $msg, $matches, PREG_UNMATCHED_AS_NULL);

    if ($ret) {
        $cmd = strtoupper($matches[1]);
        $type = ($config['enable_type']) ? strtolower($matches[2]) : "TYPE_NOT_SET_SO_STOP_ALL";
        $CI =& get_instance();
        $CI->load->model('stop_manager/Stop_manager_model', 'Stop_manager_model');

        $text = "";

        //var_dump($matches);
        switch (true) {
            case in_array($cmd, $optout_keywords):
                $ret = $CI->Stop_manager_model->add($from, $type, $msg);

                $strTemplate = ':received_command pris en compte.';
                if ($config['enable_optin'])
                    $strTemplate .= ' Pour recevoir à nouveau, répondre ":optin_command"';

                $strParams = [
                    ':received_command' => ($config['enable_type']) ? $cmd.' '.$type : $cmd,
                    ':optin_command' => ($config['enable_type']) ? $optin_keywords[0].' '.$type : $optin_keywords[0],
                ];

                $text = strtr($strTemplate, $strParams);
                if ($config['enable_autoreply_info'])
                    autoreply($from, $text);
                break;
            case (in_array($cmd, $optin_keywords) && $config['enable_optin']) :
                $ret = $CI->Stop_manager_model->delete($from, $type);

                $strTemplate = ':received_command pris en compte.';
                $strTemplate .= ' Pour ne plus recevoir, répondre ":optout_command"';

                $strParams = [
                    ':received_command' => ($config['enable_type']) ? $cmd.' '.$type : $cmd,
                    ':optout_command' => ($config['enable_type']) ? $optout_keywords[0].' '.$type : $optout_keywords[0],
                ];

                $text = strtr($strTemplate, $strParams);
                if ($config['enable_autoreply_info'])
                    autoreply($from, $text);
                break;
            default:
                $text = "Demande non valide ($msg)";
                if ($config['enable_autoreply_error'])
                    autoreply($from, $text);
                break;
        }
    } else {
        $strTemplate = "Demande non valide (:received_command). Répondre ':optout_keyword";
        if ($config['enable_type'])
            $strTemplate .= " <type>";
        $strTemplate .= "'";
        if ($config['enable_optin'])
            $strTemplate .= " ou ':optin_keyword <type>'";
        $strTemplate .= ".";
        if ($config['enable_type'])
            $strTemplate .= " Les valeurs possibles pour <type> sont: :types_valides.";
        $strTemplate .= " Par exemple ':example'.";

        $strParams = [
            ':received_command' => $msg,
            ':optout_keyword' => $optout_keywords[0],                                   // 1st keyword of the list
            ':optin_keyword' => ($config['enable_optin']) ? $optin_keywords[0] : "",    // 1st keyword of the list
            ':types_valides' => ($config['enable_type']) ? implode(', ',$types_valides) : "",
            ':example' => ($config['enable_type']) ? $optout_keywords[0]." ".$types_valides[0] : $optout_keywords[0] ,
            ];

        $text = strtr($strTemplate, $strParams);
        if ($config['enable_autoreply_error'])
            autoreply($from, $text);
    }

}

function autoreply($tel, $reply_msg)
{
    $config = stop_manager_initialize();

    // Filter rule for outgoing SMS
    if ($config['enable_autoreply_outnumber_filter']) {
        $ret = preg_match($config['autoreply_outnumber_match_rule'], $tel, $matches);
        //var_dump($ret);
        //var_dump($matches);
    }
    if ($ret) {
        $CI =& get_instance();
        $CI->load->model('Message_model');
        $data['coding'] = 'default';
        $data['class'] = '1';
        $data['dest'] = $tel;
        $data['date'] = date('Y-m-d H:i:s');
        $data['message'] = $reply_msg;
        $data['delivery_report'] = 'default';
        $data['uid'] = '1';
        $CI->Message_model->send_messages($data);
    }
}

/* End of file stop_manager.php */
/* Location: ./application/plugins/stop_manager/stop_manager.php */
