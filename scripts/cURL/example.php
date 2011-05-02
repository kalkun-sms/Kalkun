<?php
include_once("Kalkun_API.php");

$config['base_url'] = "http://localhost/kalkun/index.php/";
$config['session_file'] = "/tmp/cookies.txt";
$config['username'] = "username";
$config['password'] = "password";
$config['phone_number'] = "123456";
$config['message'] = "Test message from API";

// unicode message
// $config['coding'] = 'unicode';

$sms = new Kalkun_API($config);
$sms->run();
?>