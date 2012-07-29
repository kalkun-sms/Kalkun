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
 * Clickatell_model Class
 *
 * Handle all messages database activity 
 * for Kannel <http://clickatell.com>
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Models
 */
require_once('gammu_model'.EXT);

class Clickatell_model extends Gammu_model { 
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();
		$this->gateway = $this->config->item('gateway');
		
		if(empty($this->gateway['url']))
		{
			$this->gateway['url'] = 'http://api.clickatell.com';
		}
	}
	
    // --------------------------------------------------------------------
	
	/**
	 * Send Messages (Still POC)
	 * Using HTTP API
	 * 
	 * @return void
	 */	
	function send_messages($data)
	{
		$gateway = $this->gateway;
		file_get_contents($gateway['url'].'/http/sendmsg?user='.$gateway['username'].
			'&password='.$gateway['password'].'&api_id='.$gateway['api_id'].'&to='.$data['dest'].'&text='.urlencode($data['message']));
	}
}

/* End of file kannel_model.php */
/* Location: ./application/models/gateway/clikatell_model.php */