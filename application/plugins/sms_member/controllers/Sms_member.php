<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-3.0-or-later.html
 * @link		http://kalkun.sourceforge.net
 */

// ------------------------------------------------------------------------

/**
 * Sms_member Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Controllers
 */
include_once(APPPATH.'plugins/Plugin_controller.php');

class Sms_member extends Plugin_controller {

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->model('sms_member_model', 'plugin_model');
	}

	// --------------------------------------------------------------------

	/**
	 * Index
	 *
	 * Display list of all member
	 *
	 * @access	public
	 */
	function index()
	{
		$data['main'] = 'index';
		$data['title'] = 'SMS Member';
		$data['total_member'] = $this->plugin_model->get_member('total')->row('count');
		$data['member'] = $this->plugin_model->get_member('all')->result_array();

		$this->load->view('main/layout', $data);
	}
}
