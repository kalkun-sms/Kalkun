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
 * REST API Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/rest_api/libraries/REST_Controller.php');

class Rest_api extends REST_Controller {
	
	function __construct()
	{
		parent::__construct(FALSE);
	}
	
	/**
	* Send SMS using GET method
	* 
	* Sample call:
	* http://kalkun-url/index.php/plugin/rest_api/send_sms?phoneNumber=123456&message=testing
	* 
	*/
	function send_sms_get()
	{
		$this->load->model(array('Kalkun_model', 'Message_model'));
		$data['coding'] = ($this->get('coding') == "unicode") ? $this->get('coding') : 'default';
		$data['class'] = '1';
		$data['dest'] = $this->get('phoneNumber');
		$data['date'] = date('Y-m-d H:i:s');
		$data['message'] = $this->get('message');
		$data['delivery_report'] = 'default';
		$data['SenderID'] = ($this->get('SenderID')) ? $this->get('SenderID') : NULL;
		$data['uid'] = 1;

		$sms = $this->Message_model->send_messages($data);
		$sms['phoneNumber'] = $data['dest'];
		$sms['message'] = $data['message'];

		$this->response($sms);
	}
}
