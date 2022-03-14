<?php

/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-3.0-or-later.html
 * @link		https://kalkun-sms.github.io/
 */
// ------------------------------------------------------------------------

namespace Kalkun\Plugins\StopManager;
defined('BASEPATH') OR exit('No direct script access allowed');

require_once (APPPATH . '/plugins/Plugin_helper.php');

/**
 * Description of Config
 *
 */
class Config {

	private static $instance = null;
	private $config = NULL;
	private $keywordsOptOut = NULL;
	private $keywordsOptIn = NULL;
	private $keywordsType = NULL;

	public function __construct()
	{
		$this->config = \Plugin_helper::get_plugin_config('stop_manager');
		$this->keywordsOptOut = array_map('strtoupper', $this->config['optout_keywords']);
		$this->keywordsOptIn = array_map('strtoupper', $this->config['optin_keywords']);
		$this->keywordsType = array_map('strtolower', $this->config['type_keywords']);
	}

	public static
			function getInstance()
	{
		if (self::$instance === NULL)
		{
			self::$instance = new Config();
		}

		return self::$instance;
	}

	public function isTypeEnabled()
	{
		return $this->config['enable_type'];
	}

	public function getValidCmds()
	{
		return array_merge($this->keywordsOptOut, $this->keywordsOptIn);
		;
	}

	public function getValidTypes()
	{
		return $this->keywordsType;
	}

	public function getKeywordsOptOut()
	{
		return $this->keywordsOptOut;
	}

	public function getKeywordsOptIn()
	{
		return $this->keywordsOptIn;
	}

	public function getKeywordsType()
	{
		return $this->keywordsType;
	}

	public function getConfig($item = NULL)
	{
		if ($item !== NULL)
			return $this->config[$item];
		else
			return $this->config;
	}

	public function isAutoreplyInfoEnabled()
	{
		return $this->config['enable_autoreply_info'];
	}

	public function isAutoreplyErrorEnabled()
	{
		return $this->config['enable_autoreply_error'];
	}

}
