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
 * Sms_to_email_model Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Models
 */
class Sms_to_email_model extends CI_Model {
	function __construct()
	{
		parent::__construct();
	}

	function get_setting($uid)
	{
		$this->db->from('plugin_sms_to_email');
		$this->db->where('id_user', $uid);

		return $this->db->get();
	}

	function save_setting()
	{
		$this->db->set('email_id', $this->input->post('email_id'));
		$this->db->set('email_forward', $this->input->post('email_forward'));

		if ($this->input->post('mode') === 'edit')
		{
			$this->db->where('id_user', $this->session->userdata('id_user'));
			$this->db->update('plugin_sms_to_email');
		}
		else
		{
			$this->db->set('id_user', $this->session->userdata('id_user'));
			$this->db->insert('plugin_sms_to_email');
		}
	}
}
