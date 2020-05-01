<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
|
| Execute external script if condition match
|
| intepreter_path - path to the script interpreter (bash, python...), not path to script
| script_path - path to the script to be executed
| key - what condition we look at <sender|content>
| type - matching pattern used <equal|contain|preg_match>
| value - the value to match with
| parameter - extra parameter to send to the script <phone|content|id|time|match>,
|             each value separated by |
|
*/

// Below are some examples
// Enable one or more scripts by uncommenting `array_push($config['external_script'], $script);`

$config['external_script'] = array();

$script = array();
$script['intepreter_path'] = '/bin/sh';
$script['script_path'] = '/usr/local/reboot_server.sh';
$script['key'] = 'content';
$script['type'] = 'equal';
$script['value'] = 'reboot';
$script['parameter'] = 'phone|id|content';
//array_push($config['external_script'], $script);
unset($script);

$script = array();
$script['intepreter_path'] = '/bin/sh';
$script['script_path'] = '/usr/local/check_user.sh';
$script['key'] = 'sender';
$script['type'] = 'contain';
$script['value'] = '+62';
$script['parameter'] = 'phone|content';
//array_push($config['external_script'], $script);
unset($script);

$script = array();
$script['intepreter_path'] = '/usr/bin/python3';
$script['script_path'] = '/opt/kinetools/scripts/timing_rappel.py';
$script['key'] = 'content';
$script['type'] = 'preg_match';
// for example, message: "ACTIVER rappel 10" will match the pattern below
// and the parameter match will have a value of "10"
$script['value'] = '/\s*ACTIVER\s+rappel\s+([0-9]+)\s*/i';
$script['parameter'] = 'phone|match';
//array_push($config['external_script'], $script);
unset($script);

/* End of file external_script.php */
/* Location: ./application/plugins/external_script/config/external_script.php */
