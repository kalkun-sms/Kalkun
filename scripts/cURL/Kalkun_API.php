<?php
/*
* @author Azhari Harahap (azhari.harahap@yahoo.com)
* @date Agustus 08, 2010
* @version 0.1 alpha
* @requirement cURL
*/

class Kalkun_API {
	var $base_url = "";
	var $login_url = "login/index";
	var $sms_url = "messages/compose_process";	
	var $session_file = "/tmp/cookies.txt"; // must be writable
	var $username = "";
	var $password = "";
	var $phone_number = "";
	var $message = "";
	var $sms_mode = "0"; // 1 = flash, 0 = normal
	//var $send_date = date('Y-m-d H:i:s');
	var $coding = "";
	var $curl_id = "";
	
	function Kalkun_API($params = array())
	{
		if(count($params) > 0)
		{
			$this->curl_id = curl_init();
			$this->login_url = $params['base_url']."".$this->login_url;
			$this->sms_url = $params['base_url']."".$this->sms_url;
			$this->initialize($params);
		}
	}

	function initialize($params = array())
	{
		if (count($params) > 0)
		{
			foreach ($params as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}
		}
	}	
	
	function run()
	{
		if($this->login())
		{
			$this->send_sms();
		}
				
		$this->finish();
	}
	
	function finish()
	{
		$ch = $this->curl_id;
		curl_close($ch);
		
		if (file_exists($this->session_file))
		{
			unlink($this->session_file);
		}
	}
	
	function login()
	{
		$ch = $this->curl_id;
		curl_setopt($ch, CURLOPT_URL, $this->login_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE); 
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->session_file);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->session_file);
		
		$fields = array(
		    'username' => urlencode($this->username),
		    'password' => urlencode($this->password)
		);
		$fields_string = $this->urlify($fields);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		$output = curl_exec($ch);
		
		// Check if URL exist
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($http_code == 404) {
			$this->show_message('URL not found');
		    return FALSE;
		}
		
		if(strpos($output,"Please enter your username and password") !== false)
		{
			$this->show_message('Login failed');
			return FALSE;
		}
		else return TRUE;
	}
	
	function send_sms()
	{
		$ch = $this->curl_id;
		curl_setopt($ch, CURLOPT_URL, $this->sms_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE); 
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->session_file);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->session_file);		
		
		$sms = array(
		    'sendoption' => urlencode('sendoption3'),
		    'manualvalue' => urlencode($this->phone_number),
		    'senddateoption' => urlencode('option1'),
		    'sms_mode' => urlencode($this->sms_mode),
		    'sms_loop' => urlencode('1'),
            'validity' => urlencode('-1'),
            'unicode' => urlencode($this->coding),
		    'message' => urlencode($this->message)
		);
		$sms_field = $this->urlify($sms);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sms_field);
		$output = curl_exec($ch);	
	}
	
	//url-ify the data for the POST
	function urlify($param)
	{
		$param_string = '';
		foreach($param as $key=>$value) 
		{ $param_string .= $key.'='.$value.'&'; }
		rtrim($param_string,'&');
		return $param_string;		
	}
	
	function show_message($message)
	{
		echo $message;
	}
}

?>