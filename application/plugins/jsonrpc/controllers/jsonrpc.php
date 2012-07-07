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
 * JSONRPC Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_Controller.php');

class JSONRPC extends Plugin_Controller {
	
	function __construct()
	{
		parent::__construct(FALSE);
		$this->load->library('CI_jsonrpc', NULL, 'jsonrpc');
	}
	
	/**
	* JSONRPC server for sending sms
	*
	*/
	function send_sms()
	{
        $methods = array();
        $methods['sms.send_sms'] = array();
        $methods['sms.send_sms']['function'] = 'JSONRPC.rpc_send_sms';
        $methods['sms.send_sms']['summary']  = 'Sending an SMS';
        
        $server =& $this->jsonrpc->get_server();
        $server->define_methods($methods);
        $server->set_object($this);
        
        $server->serve();
    }

	/**
	* RPC for sending sms
	*
	*/
    function rpc_send_sms($request)
    {
    	$this->load->model(array('Kalkun_model', 'Message_model'));
        $server =& $this->jsonrpc->get_server();
		$parameters = $request;
		
		$data['coding'] = 'default';
		$data['class'] = '1';
		$data['dest'] = $parameters->phoneNumber;
		$data['date'] = date('Y-m-d H:i:s');
		$data['message'] = $parameters->message;
		$data['delivery_report'] = 'default';
		$data['uid'] = 1;
		$sms = $this->Message_model->send_messages($data);
		
        return $server->send_response($sms);
    }
    
   	/**
	* Sample JSONRPC client example
	* that consume send sms function
	*/
	function send_sms_client()
	{
		$this->load->helper('url');
		$server_url = site_url('plugin/jsonrpc/send_sms');		
		
        $client =& $this->jsonrpc->get_client();
        $client->server($server_url);
        $client->method('sms.send_sms');
		
		$request = array('phoneNumber' => '1234' , 'message' => 'Testing JSONRPC');
		$client->request($request);
		$client->send_request();
		
		echo '<pre>';
		print_r($client->get_response_object());
		echo '</pre>';
	}
}