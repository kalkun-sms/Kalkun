<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-3.0-or-later.html
 * @link		https://kalkun.sourceforge.io/
 */

// ------------------------------------------------------------------------

/**
 * Clickatell_model Class
 *
 * Handle all messages database activity
 * for Clickatell <http://clickatell.com>
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Models
 */
require_once('Nongammu_model.php');

class Clickatell_model extends Nongammu_model {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();
		$this->gateway = $this->config->item('gateway');

		if (empty($this->gateway['url']))
		{
			$this->gateway['url'] = 'http://api.clickatell.com';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Send Messages (Still POC)
	 * Using HTTP API <http://www.clickatell.com/apis-scripts/apis/http-s/>
	 *
	 * @return void
	 */
	function really_send_messages($data)
	{
		$gateway = $this->gateway;
		file_get_contents($gateway['url'].'/http/sendmsg?user='.$gateway['username'].
			'&password='.$gateway['password'].'&api_id='.$gateway['api_id'].'&to='.$data['dest'].'&text='.urlencode($data['message']));
	}
}
