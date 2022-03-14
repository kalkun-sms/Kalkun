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

/**
 * Kannel_model Class
 *
 * Handle all messages database activity
 * for Kannel <http://www.kannel.org/>
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Models
 */
require_once('Nongammu_model.php');

class Kannel_model extends Nongammu_model {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();
	}

	// --------------------------------------------------------------------

	/**
	 * Send Messages (Still POC)
	 *
	 *
	 * @return void
	 */
	function really_send_messages($data)
	{
		$gateway = $this->config->item('gateway');
		file_get_contents($gateway['url'].'/cgi-bin/sendsms?username='.$gateway['username'].
			'&password='.$gateway['password'].'&to='.$data['dest'].'&text='.urlencode($data['message']));
	}
}
