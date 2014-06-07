<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		http://kalkun.sourceforge.net/license.php
 * @link		http://kalkun.sourceforge.net
 */

// ------------------------------------------------------------------------

/**
 * Ozeking_model Class
 *
 * Handle all messages database activity 
 * for Ozeki NG <http://ozekisms.com>
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Models
 */
require_once('nongammu_model'.EXT);

class Ozeking_model extends nongammu_model { 
	
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
	 * Using HTTP API <http://ozekisms.com/index.php?ow_page_number=413>
	 * 
	 * @return void
	 */	
	function really_send_messages($data)
	{
		$gateway = $this->config->item('gateway');
		file_get_contents($gateway['url'].'/api?action=sendmessage&username='.$gateway['username'].
			'&password='.$gateway['password'].'&messagetype=SMS:TEXT&recipient='.$data['dest'].'&messagedata='.urlencode($data['message']));
	}
}

/* End of file ozeking_model.php */
/* Location: ./application/models/gateway/ozeking_model.php */
