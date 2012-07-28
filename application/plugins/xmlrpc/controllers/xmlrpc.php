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
 * XMLRPC Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_Controller.php');

class XMLRPC extends Plugin_Controller {
	
	function __construct()
	{
		parent::__construct(FALSE);
		$this->load->library('xmlrpc');
		$this->load->library('xmlrpcs');
	}
	
	/**
	* XMLRPC server for sending sms
	*
	*/
	function send_sms()
	{	
		$config['functions']['send_sms'] = array('function' => 'XMLRPC.rpc_send_sms');
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();
	}
	
	/**
	* RPC for sending sms
	*
	*/
	function rpc_send_sms($request)
	{
		$this->load->model(array('Kalkun_model', 'Message_model'));
		$parameters = $request->output_parameters();
		
		$data['coding'] = 'default';
		$data['class'] = '1';
		$data['dest'] = $parameters[0];
		$data['date'] = date('Y-m-d H:i:s');
		$data['message'] = $parameters[1];
		$data['delivery_report'] = 'default';
		$data['uid'] = 1;
		$sms = $this->Message_model->send_messages($data);
		
		return $this->xmlrpc->send_response($sms);
	}
	
	/**
	* Sample XMLRPC client example
	* that consume send sms function
	*/
	function send_sms_client()
	{
		$this->load->helper('url');
		$server_url = site_url('plugin/xmlrpc/send_sms');		
		
		$this->xmlrpc->server($server_url, 80);
		$this->xmlrpc->method('send_sms');
		//$this->xmlrpc->set_debug(TRUE);
		
		$request = array('1234', 'Testing XMLRPC');
		$this->xmlrpc->request($request);

		if (!$this->xmlrpc->send_request())
		{
			echo $this->xmlrpc->display_error();
		}
		else
		{
			echo '<pre>';
			print_r($this->xmlrpc->display_response());
			echo '</pre>';
		}
	}
}