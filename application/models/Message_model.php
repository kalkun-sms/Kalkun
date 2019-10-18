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
 * Message_model Class
 *
 * The real function should be handled by it's gateway engine
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Models
 */
class Message_model extends CI_Model { 
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */	
	function __construct()
	{
		parent::__construct();
		$gateway_config = $this->config->item('gateway');
		$gateway_class = $gateway_config['engine'].'_model';
		//require_once('gateway/'.$gateway_config['engine'].'_model'.EXT);		
		$this->load->model('gateway/'.$gateway_class,'gate');
		$this->gateway = $this->gate;
	}
	
	public function __call($name, $arguments)
    {
    	$res = call_user_func_array(array($this->gateway, $name), $arguments);    	
    	return $res;
    }
    
}

/* End of file messages_model.php */
/* Location: ./application/models/messages_model.php */