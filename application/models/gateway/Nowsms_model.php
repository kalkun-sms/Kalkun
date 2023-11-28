<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-2.0-or-later.html
 * @link		https://kalkun.sourceforge.io/
 */

// ------------------------------------------------------------------------

/**
 * Nowsms_model Class
 *
 * Handle all messages database activity
 * for Nowsms <http://nowsms.com>
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Models
 */
require_once('Nongammu_model.php');

class Nowsms_model extends Nongammu_model {

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
	 * Using HTTP API <http://www.nowsms.com/doc/submitting-sms-messages/url-parameters>
	 *
	 * @return void
	 */
	function really_send_messages($data)
	{
		$gateway = $this->config->item('gateway');
		file_get_contents($gateway['url'].'/?User='.$gateway['username'].
			'&Password='.$gateway['password'].'&PhoneNumber='.$data['dest'].'&Text='.urlencode($data['message']));
	}
}
