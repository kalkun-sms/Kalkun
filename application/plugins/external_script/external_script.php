<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Plugin Name: External Script
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Execute external script to create your own logic application
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

function external_script_initialize()
{
	$CI =& get_instance();

	$CI->load->add_package_path(APPPATH.'plugins/external_script', FALSE);
	$CI->load->config('external_script', TRUE);

	return $CI->config->item('external_script', 'external_script');
}

// Add hook for incoming message
add_action("message.incoming.before", "external_script", 12);

/**
* Function called when plugin first activated
* Utility function must be prefixed with the plugin name
* followed by an underscore.
*
* Format: pluginname_activate
*
*/
function external_script_activate()
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
function external_script_deactivate()
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
function external_script_install()
{
    return true;
}

function external_script($sms)
{
	$scripts = external_script_initialize();
	$phone = $sms->SenderNumber;
	$content = $sms->TextDecoded;
	$id = $sms->ID;
	$time = $sms->ReceivingDateTime;
	$match = NULL; //The result of a preg_match capture

	// Load all rules
	foreach($scripts as $rule)
	{
		$intepreter_path = $rule['intepreter_path'];
		$script_path = $rule['script_path'];
		$value=$parameter="";

		// evaluate rule key
		switch($rule['key'])
		{
			case 'sender':
				$value = $phone;
			break;

			case 'content':
				$value = $content;
			break;
		}

		// evaluate rule type
		switch($rule['type'])
		{
			case 'match':
			case 'equal':
				$is_valid = is_equal($rule['value'], $value);
			break;

			case 'contain':
				$is_valid = is_contain($rule['value'], $value);
			break;

			case 'preg_match':
				$ret = is_preg_match($rule['value'], $value);
				$is_valid = $ret[0];
				$match = $ret[1][1];
			break;
		}

		// if we got valid rules
		if ($is_valid)
		{
			// build extra parameters
			if (!empty($rule['parameter']))
			{
				$valid_param = array('phone','content','id','time','match');
				$param = explode("|", $rule['parameter']);

				foreach ($param as $tmp)
				{
					if (in_array($tmp, $valid_param))
					{
						$parameter.=" ".escapeshellarg(${$tmp});
					}
				}
			}

			// execute it
			exec(escapeshellcmd($intepreter_path." ".$script_path." ".$parameter));
		}
	}
}

function is_equal($subject, $matched)
{
	if ($subject===$matched) return TRUE;
	else return FALSE;
}

function is_contain($subject, $matched)
{
	if (!strstr($matched, $subject)) return FALSE;
	else return TRUE;
}

function is_preg_match($pattern, $subject)
{
	$ret = preg_match($pattern, $subject, $matches, PREG_UNMATCHED_AS_NULL);
	if ($ret === 1) return array(TRUE, $matches);
	else return array(FALSE, NULL);
}

/* End of file external_script.php */
/* Location: ./application/plugins/external_script/external_script.php */
