<?php
/**
* Plugin Name: Stop Manager
* Plugin URI: https://github.com/kalkun-sms/Kalkun/wiki/Plugin%3A-Stop-manager
* Version: 1.0
* Description: Manage incoming SMS containing STOP
* Author: tenzap
* Author URI: https://github.com/tenzap
*/

require_once (APPPATH.'plugins/Plugin_helper.php');
Plugin_helper::autoloader();

use Kalkun\Plugins\StopManager\Config;
use Kalkun\Plugins\StopManager\MsgIncoming;
use Kalkun\Plugins\StopManager\MsgOutgoing;

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


/**
 * Cleanup the outgoing message
 *  - the list of recipient (remove those who have opted out)
 *  - the content (remove the type)
 *
 * @param array $all (an array containing $dest & $data)
 * @return array (an array containing $dest & $data)
 */
function stop_manager_cleanup_outgoing($all)
{
	$stopCfg = Config::getInstance();
	$stopMsgOutgoing = new MsgOutgoing($all);

	$dest = $all[0];
	$data = $all[1];

	$CI = &get_instance();
	// Get the list of numbers having "STOP" for this type of SMS
	$CI->load->model('stop_manager/Stop_manager_model', 'Stop_manager_model');
	$type = $stopCfg->isTypeEnabled() ? $stopMsgOutgoing->getType() : NULL;
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

	$data['message'] = $stopMsgOutgoing->getCleanedMsg();

	return array($dest, $data);
}

/**
 * Analyse an incoming message and store/remove in the Stop_manager database
 *
 * @param
 * @return void
 */
function stop_manager_incoming($sms)
{
	// On message reception, if it is a STOP message (eg STOP rappel)
	// Put it to the STOP table

	$stopCfg = Config::getInstance();
	$stopMsgIncoming = new MsgIncoming($sms);

	if ($stopMsgIncoming->isValidStopMessage())
	{
		$CI = &get_instance();
		$CI->load->model('stop_manager/Stop_manager_model', 'Stop_manager_model');

		// Add to DB in case of OptOut
		if ($stopMsgIncoming->isOptOut())
		{
			$ret = $CI->Stop_manager_model->add($stopMsgIncoming->getParty(), $stopMsgIncoming->getType(), $stopMsgIncoming->getOrigMsg());
		}

		// Delete From DB in case of OptIn
		if ($stopMsgIncoming->isOptIn())
		{
			$ret = $CI->Stop_manager_model->delete($stopMsgIncoming->getParty(), $stopMsgIncoming->getType());
		}

		// Send auto reply
		if ($stopCfg->isAutoreplyInfoEnabled())
		{
			autoreply($stopMsgIncoming->getParty(), $stopMsgIncoming->getAutoReplyMsg());
		}
	}
	else
	{
		if ($stopCfg->isAutoreplyErrorEnabled())
		{
			autoreply($stopMsgIncoming->getParty(), $stopMsgIncoming->getAutoReplyMsg());
		}
	}
}

function autoreply($tel, $reply_msg)
{
	$config = Config::getInstance()->getConfig();

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
