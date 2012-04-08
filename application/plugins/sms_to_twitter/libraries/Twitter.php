<?php

/**
 * CodeIgniter Twitter API Library (http://www.haughin.com/code/twitter)
 * 
 * Author: Elliot Haughin (http://www.haughin.com), elliot@haughin.com
 *
 * ========================================================
 * REQUIRES: php5, curl, json_decode
 * ========================================================
 * 
 * VERSION: 3.1 (2009-05-01)
 * LICENSE: GNU GENERAL PUBLIC LICENSE - Version 2, June 1991
 * 
 * ========================================================
 * MODIFIED BY: Azhari Harahap <azhari@harahap.us>
 * To support oauth callback
 * Tested on CI 1.7.2
 * ========================================================
 **/
 
class Twitter {

	/**
	 * There are 3 types of requests we can make.
	 * 1. Non-Authenticated requests - require no form of authentication to use
	 * 2. Basic-Auth requests - uses a username and password for a user to make the request
	 * 3. oAuth requests - uses oAuth to make requests
	 *
	 **/

	private $_url_api			= 'http://twitter.com/';
	private $_url_api_search	= 'http://search.twitter.com/';
	private $_api_format		= 'json';

	private $_methods = array(
							'statuses/public_timeline'		=> array('http' => 'get',	'auth' => FALSE),
							'statuses/friends_timeline'		=> array('http' => 'get',	'auth' => TRUE),
							'statuses/user_timeline'		=> array('http' => 'get',	'auth' => FALSE),
							'statuses/mentions'				=> array('http' => 'get',	'auth' => TRUE),
							'statuses/show'					=> array('http' => 'get',	'auth' => FALSE),
							'statuses/update'				=> array('http' => 'post',	'auth' => TRUE),
							'statuses/destroy'				=> array('http' => 'post',	'auth' => TRUE),
							'users/show'					=> array('http' => 'get',	'auth' => FALSE),
							'statuses/friends'				=> array('http' => 'get',	'auth' => FALSE),
							'statuses/followers'			=> array('http' => 'get',	'auth' => TRUE),
							'direct_messages'				=> array('http' => 'get',	'auth' => TRUE),
							'direct_messages/sent'			=> array('http' => 'get',	'auth' => TRUE),
							'direct_messages/new'			=> array('http' => 'post',	'auth' => TRUE),
							'direct_messages/destroy'		=> array('http' => 'post',	'auth' => TRUE),
							'friendships/create'			=> array('http' => 'post',	'auth' => TRUE),
							'friendships/destroy'			=> array('http' => 'post',	'auth' => TRUE),
							'friendships/exists'			=> array('http' => 'get',	'auth' => TRUE),
							'account/verify_credentials'	=> array('http' => 'get',	'auth' => TRUE),
							'account/rate_limit_status'		=> array('http' => 'get',	'auth' => FALSE),
							'account/end_session'			=> array('http' => 'post',	'auth' => TRUE),
							'account/update_delivery_device'=> array('http' => 'post',	'auth' => TRUE),
							'account/update_profile_colors' => array('http' => 'post',	'auth' => TRUE),
							'account/update_profile'		=> array('http' => 'post',	'auth' => TRUE),
							'favorites'						=> array('http' => 'get',	'auth' => TRUE),
							'favorites/create'				=> array('http' => 'post',	'auth' => TRUE),
							'notifications/follow'			=> array('http' => 'post',	'auth' => TRUE),
							'notifications/leave'			=> array('http' => 'post',	'auth' => TRUE),
							'blocks/create'					=> array('http' => 'post',	'auth' => TRUE),
							'blocks/destroy'				=> array('http' => 'post',	'auth' => TRUE),
							'help/test'						=> array('http' => 'get',	'auth' => FALSE)

							//'account/update_profile_image'	=> array('http' => 'post',	'auth' => TRUE),
							//'account/account/update_profile_background_image'	=> array('http' => 'post',	'auth' => TRUE),

						);

	private $_conn;
	public $oauth;

	function __construct()
	{
		$this->_conn = new Twitter_Connection();
	}

	public function auth($username, $password)
	{
		$this->deauth();
		$this->_conn->auth($username, $password);
	}

	public function oauth($consumer_key, $consumer_secret, $access_token = NULL, $access_token_secret = NULL, $callback = NULL)
	{
		$this->deauth();
		$this->oauth = new EpiTwitter($consumer_key, $consumer_secret, $access_token, $access_token_secret);
		$this->oauth->setCallback($callback);
		$this->oauth->setToken($access_token, $access_token_secret);

		if ( $access_token === NULL && $access_token_secret === NULL && !isset($_GET['oauth_token']) )
		{
			$url = $this->oauth->getAuthorizationUrl();

			header('Location: '.$url);
		}
		elseif ( $access_token === NULL && $access_token_secret === NULL && isset($_GET['oauth_token']) )
		{
			$access_token = $_GET['oauth_token'];
			$this->oauth->setToken($access_token);
			$info = $this->oauth->getAccessToken();
			if (is_object($info))
			{
				$response = array(
								'access_token' => $info->oauth_token,
								'access_token_secret' => $info->oauth_token_secret
							);
				$this->oauth->setToken($response['access_token'], $response['access_token_secret']);
				return $response;
			}
		}

		return TRUE;
	}

	public function deauth()
	{
		$this->oauth = NULL;
		$this->_conn->deauth();
	}

	public function search($method, $params = array())
	{
		$url = $this->_url_api_search.$method.'.'.$this->_api_format;

		return $this->_conn->get($url, $params);
	}

	public function call($method, $params = array())
	{
		// Firstly, assume we are using a GET non-authenticated call.

		$http = 'get';
		$auth = FALSE;

		// Now we get our http and auth options from the methods array.

		if ( isset($this->_methods[$method]) )
		{
			$http = $this->_methods[$method]['http'];
			$auth = $this->_methods[$method]['auth'];
		}

		if ( $auth === TRUE && ( $this->_conn->authed() || $this->oauth === NULL) )
		{
			// method requires auth, and we have not authed yet.
			return NULL;
		}

		if ( $this->oauth !== NULL )
		{
			$parts = explode('/', $method);
			
			if ( count($parts) > 1 )
			{
				$method_string = $http.'_'.$parts[0].ucfirst($parts[1]);
			}
			else
			{
				$method_string = $http.'_'.$parts[0];
			}
			
			$data = $this->oauth->$method_string($params);
			return $data->_result;
		}

		$url = $this->_url_api . $method . '.' .$this->_api_format;

		return $this->_conn->$http($url, $params);
	}
}

class Twitter_Connection {

	private $_curl						= NULL;
	private $_auth_method				= NULL;
	private $_auth_user					= NULL;
	private $_auth_pass					= NULL;

	function __construct()
	{
	}

	private function _init()
	{
		$this->_curl = curl_init();

		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, TRUE);

		if ( $this->_auth_method == 'basic' )
		{
			curl_setopt($this->_curl, CURLOPT_USERPWD, "$this->_auth_user:$this->_auth_pass");
		}
	}

	public function authed()
	{
		if ( $this->_auth_method === NULL ) return FALSE;

		return TRUE;
	}

	public function auth($username, $password)
	{
		$this->deauth();

		$this->_auth_method = 'basic';
		$this->_auth_user	= $username;
		$this->_auth_pass	= $password;
	}

	public function deauth($auth_method = NULL)
	{
		if ( $auth_method == 'basic' || NULL )
		{
			$this->_auth_user			= NULL;
			$this->_auth_pass			= NULL;
		}

		$this->_auth_method			= NULL;
	}

	public function get($url, $params = array())
	{
		$this->_init();

		if ( is_array($params) && !empty($params) )
		{
			$url = $url . '?' . $this->_params_to_query($params);
		}

		curl_setopt($this->_curl, CURLOPT_URL, $url);

		return $this->deserialize(curl_exec($this->_curl));
	}

	public function post($url, $params = array())
	{
		$this->_init();

		if ( is_array($params) && !empty($params) )
		{
			curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $this->_params_to_query($params));
		}

		curl_setopt($this->_curl, CURLOPT_POST, TRUE);
		curl_setopt($this->_curl, CURLOPT_URL, $url);

		return $this->deserialize(curl_exec($this->_curl));
	}

	private function _params_to_query($params)
	{
		if ( !is_array($params) || empty($params) )
		{
			return '';
		}

		$query = '';

		foreach	( $params as $key => $value )
		{
			$query .= $key . '=' . $value . '&';
		}

		return substr($query, 0, strlen($query) - 1);;
	}

	private function deserialize($result)
	{
		return json_decode($result);
	}
}

/*
 * From here on, it's the EpiTwitter class and its dependencies.
 * It works pretty well, but the goal is to eventually port this all to fresh code, using common connection
 * and response libraries to the basic auth.
 */

include_once('Epi.php');