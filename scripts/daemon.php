<?php
// Make sure it's run from CLI
if (php_sapi_name() != 'cli' && ! empty($_SERVER['REMOTE_ADDR']))
{
	exit('Access Denied.');
}

// Please configure this
$url = 'http://localhost/kalkun';

$result = file_get_contents($url.'/index.php/daemon/message_routine/');

echo $result;
