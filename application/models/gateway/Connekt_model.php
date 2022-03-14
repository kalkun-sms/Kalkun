<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-3.0-or-later.html
 * @link		https://kalkun-sms.github.io/
 */

// ------------------------------------------------------------------------

/**
 * Connekt_model Class
 *
 * Handle all messages database activity
 * for Connekt <https://github.com/kingster/connekt>
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Models
 */
require_once('Nongammu_model.php');

class Connekt_model extends Nongammu_model {

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
	 * @return void
	 */
	function really_send_messages($data)
	{
		$gateway = $this->config->item('gateway');
		$p = $data['dest'];

		$payload = array (
			'channelData' => array (
				'type' => 'SMS',
				'body' => $data['message'],
			),
			'channelInfo' => array (
				'receivers' => array($p),
				'type' => 'SMS',
			),
			'sla' => 'H',
		);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $gateway['url'],
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => TRUE,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => json_encode($payload),
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'x-api-key: '.$gateway['api_id']
			),
		));

		$response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);

		$is_succes = ($httpcode >= 200 && $httpcode < 300) ? TRUE : FALSE;
		if ($is_succes)
		{
			return  $result[] = array('phone' => $p, 'msg' => $response, 'result' => $is_succes);
		}
		else
		{
			return $$response;
		}
	}
}
