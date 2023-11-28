<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-2.0-or-later.html
 * @link		https://kalkun.sourceforge.io/
 */

// ------------------------------------------------------------------------

/**
 * Jsonrpc Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */

class Evaluator implements Datto\JsonRpc\Evaluator {

	public function evaluate($method, $arguments)
	{
		if ($method === 'sms.send_sms')
		{
			return self::send_sms($arguments);
		}

		throw new MethodException();
	}

	private static function send_sms($arguments)
	{
		if (empty($arguments))
		{
			throw new ArgumentException();
		}

		$CI = &get_instance();
		$CI->load->model(array('Kalkun_model', 'Message_model'));

		$data['class'] = '1';
		$data['dest'] = $arguments['phoneNumber'];
		$data['date'] = date('Y-m-d H:i:s');
		$data['message'] = $arguments['message'];
		$data['delivery_report'] = 'default';
		$data['uid'] = 1;
		$sms = $CI->Message_model->send_messages($data);

		return implode(' ', $sms);
	}
}
