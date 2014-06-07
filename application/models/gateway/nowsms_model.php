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
 * Nowsms_model Class
 *
 * Handle all messages database activity 
 * for Nowsms <http://nowsms.com>
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Models
 */
require_once('nongammu_model'.EXT);

class Nowsms_model extends nongammu_model { 
	
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

/* End of file nowsms_model.php */
/* Location: ./application/models/gateway/nowsms_model.php */
