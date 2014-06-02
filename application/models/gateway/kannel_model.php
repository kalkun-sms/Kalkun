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
 * Kannel_model Class
 *
 * Handle all messages database activity 
 * for Kannel <http://www.kannel.org/>
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Models
 */
require_once('nongammu_model'.EXT);

class Kannel_model extends nongammu_model { 
	
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

/* End of file kannel_model.php */
/* Location: ./application/models/gateway/kannel_model.php */
