<?php
/**
* Plugin Name: External Script
* Plugin URI: http://azhari.harahap.us
* Version: 0.1
* Description: Execute external script to create your own logic application
* Author: Azhari Harahap
* Author URI: http://azhari.harahap.us
*/

/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
|
| Execute external script if condition match
| 
| path - path of the shell program (bash), not path to script to be executed
| name - the script name to be executed
| key - what condition we looking at (sender or content)
| type - pattern matching used (match or contain)
| value - the value to matching with
| parameter - extra parameter to send to the script (phone,content,id,time), each value divided by |
| 
*/
function external_script_initialize()
{
	$config['ext_script_path'] = '/bin/sh';
	$config['ext_script'][0]['name'] = '/usr/local/reboot_server.sh';
	$config['ext_script'][0]['key'] = 'content';
	$config['ext_script'][0]['type'] = 'match';
	$config['ext_script'][0]['value'] = 'reboot';
	$config['ext_script'][0]['parameter'] = 'phone|id|content';
	
	$config['ext_script'][1]['name'] = '/usr/local/check_user.sh';
	$config['ext_script'][1]['key'] = 'sender';
	$config['ext_script'][1]['type'] = 'contain';
	$config['ext_script'][1]['value'] = '+62';
	$config['ext_script'][1]['parameter'] = 'phone|content';
	
	return $config;
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
	$config = external_script_initialize();
	$phone = $sms->SenderNumber;
	$content = $sms->TextDecoded;
	$id = $sms->ID;
	$time = $sms->ReceivingDateTime;
	$shell_path = $config['ext_script_path'];
			
	// Load all rules	
	foreach($config['ext_script'] as $rule)
	{
		$script_name = $rule['name'];
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
				$is_valid = is_match($rule['value'], $value);
			break;
			
			case 'contain':
				$is_valid = is_contain($rule['value'], $value);
			break;
		}
		
		// if we got valid rules
		if ($is_valid)
		{
			// build extra parameters
			if (!empty($rule['parameter']))
			{
				$valid_param = array('phone','content','id','time');
				$param = explode("|", $rule['parameter']);
				
				foreach ($param as $tmp)
				{
					if (in_array($tmp, $valid_param))
					{
						$parameter.=" ".${$tmp};
					}
				}
			}
			
			// execute it
			exec($shell_path." ".$script_name." ".$parameter);
		}
	}	
}

function is_match($subject, $matched)
{
	if ($subject===$matched) return TRUE;
	else return FALSE;
}

function is_contain($subject, $matched)
{
	if (!strstr($matched, $subject)) return FALSE;
	else return TRUE;
}

/* End of file external_script.php */
/* Location: ./application/plugins/external_script/external_script.php */