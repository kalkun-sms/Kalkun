<?php
/*
* @author Azhari Harahap (azhari.harahap@yahoo.com)
* @date Agustus 08, 2010
* @version 0.1 alpha
* @requirement cURL
*/

class Kalkun_API {

	var $base_url = '';
	var $login_url = 'login/index';
	var $sms_url = 'messages/compose_process';
	var $csrf_hash_url = 'kalkun/get_csrf_hash';
	var $session_file = '/tmp/cookies.txt'; // must be writable
	var $username = '';
	var $password = '';
	var $phone_number = '';
	var $message = '';
	var $sms_mode = '0'; // 1 = flash, 0 = normal
	//var $send_date = date('Y-m-d H:i:s');
	var $curl_id = '';

	function Kalkun_API($params = array())
	{
		if (count($params) > 0)
		{
			$this->curl_id = curl_init();
			$this->login_url = $params['base_url'].''.$this->login_url;
			$this->sms_url = $params['base_url'].''.$this->sms_url;
			$this->csrf_hash_url = $params['base_url'].''.$this->csrf_hash_url;
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
		if ($this->login())
		{
			$http_code = $this->send_sms();
			if ($http_code < 400)
			{
				$this->show_message("Message queued successfully.\n");
			}
			else
			{
				$this->show_message("Error queuing the message (HTTP_CODE: ${http_code}).\n");
			}
		}
		else
		{
			$this->show_message('Error during login');
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
		$csrf_hash = $this->get_csrf_hash_from_login_form();

		$ch = $this->curl_id;
		curl_setopt($ch, CURLOPT_URL, $this->login_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->session_file);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->session_file);

		$fields = array(
			'username' => urlencode($this->username),
			'password' => urlencode($this->password),
			'kalkun_csrf_tkn' => $csrf_hash,
		);

		$fields_string = $this->urlify($fields);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		$output = curl_exec($ch);

		// Check HTTP Response code
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($http_code >= 400)
		{
			$this->show_message('ERROR: HTTP_CODE: '.$http_code);
			return FALSE;
		}

		if (strpos($output, 'Please enter your username and password') !== FALSE)
		{
			$this->show_message('Login failed');
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}

	function send_sms()
	{
		$csrf_hash = $this->get_csrf_hash();
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
			'message' => urlencode($this->message),
			'kalkun_csrf_tkn' => $csrf_hash,
		);
		$sms_field = $this->urlify($sms);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sms_field);
		$output = curl_exec($ch);

		return curl_getinfo($ch, CURLINFO_HTTP_CODE);
	}

	function get_csrf_hash()
	{
		$ch = $this->curl_id;
		curl_setopt($ch, CURLOPT_URL, $this->csrf_hash_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_POST, FALSE);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->session_file);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->session_file);
		$output = curl_exec($ch);
		return json_decode($output);
	}

	function get_csrf_hash_from_login_form()
	{
		$ch = $this->curl_id;
		curl_setopt($ch, CURLOPT_URL, $this->login_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_POST, FALSE);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->session_file);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->session_file);

		$output = curl_exec($ch);

		// Extract the CSRF Hash from the <input> tag of the HTML
		$dom = new DOMDocument();
		$dom->loadHTML($output);
		$xp = new DOMXpath($dom);
		$nodes = $xp->query('//input[@name="kalkun_csrf_tkn"]');
		$node = $nodes->item(0);

		return $node->getAttribute('value');
	}

	//url-ify the data for the POST
	function urlify($param)
	{
		$param_string = '';
		foreach ($param as $key => $value)
		{
			$param_string .= $key.'='.$value.'&';
		}
		rtrim($param_string, '&');
		return $param_string;
	}

	function show_message($message)
	{
		echo $message;
	}
}
