<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		joseph mazigo
 * @license		http://kalkun.sourceforge.net/license.php
 * @link		http://kalkun.sourceforge.net
 * @link        http://josephmazigo.com
 */

// ------------------------------------------------------------------------

/**
 * panacea_model Class
 *
 * Handle all messages database activity 
 * for Panacea Mobile <http://panaceamobile.com>
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Models
 */
require_once('nongammu_model'.EXT);

class panacea_model extends nongammu_model { 
	
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
	 * Using HTTP API <http://www.panaceamobile.com/docs/PanaceaApi/PanaceaApi.html>
	 * 
	 * @return void
	 *
	 * replace xxx from &from=xxx with your sender id
	 */	
	function really_send_messages($data)
	{
		$gateway = $this->config->item('gateway');
		file_get_contents($gateway['url'].'/json?action=message_send&username='.$gateway['username'].
			'&password='.$gateway['password'].'&to='.$data['dest'].'&text='.urlencode($data['message']).'&from=xxx');
	}
}

/* End of file panacea_model.php */
/* Location: ./application/models/gateway/panacea_model.php */
