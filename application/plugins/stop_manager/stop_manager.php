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
add_action('message.outgoing_dest_data', 'stop_manager_cleanup_outgoing', 1);
add_action('message.incoming.before', 'stop_manager_incoming', 1);

function stop_manager_activate()
{
	return TRUE;
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
	return TRUE;
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
	$CI = &get_instance();
	$CI->load->helper('kalkun');
	// check if table already exists
	if ( ! $CI->db->table_exists('plugin_stop_manager'))
	{
		$db_driver = $CI->db->platform();
		$db_prop = get_database_property($db_driver);
		execute_sql(APPPATH.'plugins/stop_manager/media/'.$db_prop['file'].'_stop_manager.sql');
	}
	return TRUE;
}


//--------------------------------------------------------------------------
// CONFIGURATION
//--------------------------------------------------------------------------
function stop_manager_initialize()
{
	$CI = &get_instance();

	$CI->load->add_package_path(APPPATH.'plugins/stop_manager', FALSE);
	$CI->load->config('stop_manager', TRUE);

	return $CI->config->config['stop_manager'];
}

function stop_manager_cleanup_outgoing($all)
{
	$config = stop_manager_initialize();

	$dest = $all[0];
	$data = $all[1];

	$CI = &get_instance();

	// Get the type of the SMS (rappel, annul...)
	$msg = $data['message'];
	// Be careful! Kalkun may append $config['append_username_message'] to all messages.
	$ret_match = NULL;
	if ($CI->config->item('append_username'))
	{
		$ret_match = preg_match('/^(.*)~(.+)~.*/', $msg, $matches, PREG_UNMATCHED_AS_NULL);
	}
	else
	{
		$ret_match = preg_match('/^(.*)~(.+)~$/', $msg, $matches, PREG_UNMATCHED_AS_NULL);
	}

	$type = NULL;
	if ($ret_match && isset($matches[2]) && $config['enable_type'])
	{
		$type = $matches[2];
	}
	if (is_null($type))
	{
		// type of SMS (for filtering) is not set yet.
		// The message is sent    if we enabled  the use of type ($config['enable_type'])
		// The message is dropped if we disabled the use of type ($config['enable_type']) and if it is in blacklist
		if ( ! $config['enable_type'])
		{
			// Will drop all numbers that are in stop_manager whatever the value of type
			//$type = "%";

			// Will drop all numbers that are in stop_manager having been recorded as TYPE_NOT_SET_SO_STOP_ALL
			$type = 'TYPE_NOT_SET_SO_STOP_ALL';
		}
		else
		{
			// IGNORE_STOP_MANAGER is just a fake value that should never match something in the table,
			// this is to keep the message
			$type = 'IGNORE_STOP_MANAGER';
		}
	}

	// Get ths list of numbers having "STOP" for this type of SMS
	$CI->load->model('stop_manager/Stop_manager_model', 'Stop_manager_model');
	$db_result = $CI->Stop_manager_model->get_num_for_type($type)->result_array();
	$blocked_numbers = array();

	foreach ($db_result as $row)
	{
		$blocked_numbers[] = $row['destination_number'];
	}

	// Remove the phone no. if the recipient is in the STOP table for this type of sms
	foreach ($dest as $key => $number)
	{
		foreach ($blocked_numbers as $n)
		{
			if ($n === $number)
			{
				unset($dest[$key]);
			}
		}
	}

	// Remove inside the message the "tag" that permits to know what type of message it is
	// eg. "~rappel~" at the end of the message
	if ($ret_match && isset($matches[1]))
	{
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

	// On message reception, if it is a STOP message (eg STOP rappel)
	// Put it to the STOP table
	$msg = $sms->TextDecoded;
	$from = $sms->SenderNumber;
	//$msg_user = $sms->msg_user;

	$cmds_valides = array_merge($optout_keywords, $optin_keywords);
	$types_valides = $type_keywords;

	$types_reg = implode('|', $types_valides);
	$cmds_reg = implode('|', $cmds_valides);

	$ret = NULL;
	if ($config['enable_type'])
	{
		$ret = preg_match('/\b('.$cmds_reg.')\s*('.$types_reg.')\b/i', $msg, $matches, PREG_UNMATCHED_AS_NULL);
	}
	else
	{
		$ret = preg_match('/\b('.$cmds_reg.')\b/i', $msg, $matches, PREG_UNMATCHED_AS_NULL);
	}

	$CI = &get_instance();

	// language
	$CI->load->helper(['language', 'i18n']);
	// We cannot determine the language of a specific user since this is called on incoming message
	// So the language to use by this robot is read from plugin config
	$lang = $config['autoreply_language'];
	$CI->load->add_package_path(APPPATH.'plugins/stop_manager', FALSE);
	$CI->load->language('stop_manager', $lang);

	if ($ret === 1)
	{
		$cmd = strtoupper($matches[1]);
		$type = ($config['enable_type']) ? strtolower($matches[2]) : 'TYPE_NOT_SET_SO_STOP_ALL';
		$CI->load->model('stop_manager/Stop_manager_model', 'Stop_manager_model');

		$text = '';

		//var_dump($matches);
		switch (TRUE) {
			case in_array($cmd, $optout_keywords):
				// Received opt-out message
				$ret = $CI->Stop_manager_model->add($from, $type, $msg);

				if ($config['enable_optin'])
				{
					$text = tr(
						'{0} taken into account. To opt-in again, send "{1}".',
						NULL,
						($config['enable_type']) ? $cmd.' '.$type : $cmd,
						($config['enable_type']) ? $optin_keywords[0].' '.$type : $optin_keywords[0]
					);
				}
				else
				{
					$text = tr(
						'{0} taken into account.',
						NULL,
						($config['enable_type']) ? $cmd.' '.$type : $cmd
					);
				}

				if ($config['enable_autoreply_info'])
				{
					autoreply($from, $text);
				}
				break;
			case (in_array($cmd, $optin_keywords) && $config['enable_optin']) :
				// Received opt-in message
				$ret = $CI->Stop_manager_model->delete($from, $type);

				$text = tr(
					'{0} taken into account. To opt-out, send "{1}".',
					NULL,
					($config['enable_type']) ? $cmd.' '.$type : $cmd,
					($config['enable_type']) ? $optout_keywords[0].' '.$type : $optout_keywords[0]
				);

				if ($config['enable_autoreply_info'])
				{
					autoreply($from, $text);
				}
				break;
			default:
				$text = tr('Invalid request.')." (${msg})";
				if ($config['enable_autoreply_error'])
				{
					autoreply($from, $text);
				}
				break;
		}
	}
	else
	{
		if ($config['enable_autoreply_error'])
		{
			if ($config['enable_type'])
			{
				if ($config['enable_optin'])
				{
					$text = tr(
						'Request not valid ({0}). Send "{1} or {2} <type>". Possible values for <type> are: {3}. For example "{4}".',
						NULL,
						$msg,
						$optout_keywords[0],
						$optin_keywords[0],
						implode(', ', $types_valides),
						$optout_keywords[0].' '.$types_valides[0]
					);
				}
				else
				{
					$text = tr(
						'Request not valid ({0}). Send "{1} <type>". Possible values for <type> are: {2}. For example "{3}".',
						NULL,
						$msg,
						$optout_keywords[0],
						implode(', ', $types_valides),
						$optout_keywords[0].' '.$types_valides[0]
					);
				}
			}
			else
			{
				if ($config['enable_optin'])
				{
					$text = tr(
						'Request not valid ({0}). Send "{1}" or "{2}". For example "{3}".',
						NULL,
						$msg,
						$optout_keywords[0],
						$optin_keywords[0],
						$optout_keywords[0]
					);
				}
				else
				{
					$text = tr(
						'Request not valid ({0}). Send "{1}".',
						NULL,
						$msg,
						$optout_keywords[0]
					);
				}
			}

			autoreply($from, $text);
		}
	}
}

function autoreply($tel, $reply_msg)
{
	$config = stop_manager_initialize();

	$ret = NULL;
	// Filter rule for outgoing SMS
	if ($config['enable_autoreply_outnumber_filter'])
	{
		$ret = preg_match($config['autoreply_outnumber_match_rule'], $tel, $matches);
		//var_dump($ret);
		//var_dump($matches);
	}
	if ( ! $config['enable_autoreply_outnumber_filter'] || $ret === 1)
	{
		$CI = &get_instance();
		$CI->load->model('Message_model');
		$data['class'] = '1';
		$data['dest'] = $tel;
		$data['date'] = date('Y-m-d H:i:s');
		$data['message'] = $reply_msg;
		$data['delivery_report'] = 'default';
		$data['uid'] = '1';
		$CI->Message_model->send_messages($data);
	}
}
