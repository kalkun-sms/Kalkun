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
 * Sms_to_xmpp_model Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Models
 */
class Sms_to_xmpp_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}

	function check_status($uid)
	{
		$exist = FALSE;
		$this->db->from('plugin_sms_to_xmpp');
		$this->db->where('id_user', $uid);

		if ($this->db->count_all_results() === 1)
		{
			$exist = TRUE;
		}
		return $exist;
	}

	function get_xmpp($uid)
	{
		$secret = FALSE;
		$this->db->from('plugin_sms_to_xmpp');
		$this->db->where('id_user', $uid);
		$user_xmpp = $this->db->get();
		if ($user_xmpp->num_rows() === 1)
		{
			$secret['xmpp_host'] = $user_xmpp->row('xmpp_host');
			$secret['xmpp_port'] = $user_xmpp->row('xmpp_port');
			$secret['xmpp_username'] = $user_xmpp->row('xmpp_username');
			$secret['xmpp_password'] = $user_xmpp->row('xmpp_password');
			$secret['xmpp_server'] = $user_xmpp->row('xmpp_server');
		}
		return $secret;
	}

	function get_xmpp_account_by_phone($number)
	{
		$secret = FALSE;
		$this->db->from('user');
		$this->db->where('phone_number', $number);
		$user = $this->db->get();
		if ($user->num_rows() === 1)
		{
			$secret = $this->get_xmpp($user->row('id_user'));
		}
		return $secret;
	}

	function save_xmpp()
	{
		$this->load->library('encryption');
		$encrypted_pwd = $this->encryption->encrypt($this->input->post('xmpp_password'));
		if ($encrypted_pwd === FALSE)
		{
			log_message('error', 'sms_to_xmpp: problem during encryption.');
			show_error('sms_to_xmpp: problem during encryption.', 500, '500 Internal Server Error');
		}
		$this->db->set('xmpp_host', $this->input->post('xmpp_host'));
		$this->db->set('xmpp_port', $this->input->post('xmpp_port'));
		$this->db->set('xmpp_username', $this->input->post('xmpp_username'));
		$this->db->set('xmpp_password', $encrypted_pwd);
		$this->db->set('xmpp_server', $this->input->post('xmpp_server'));
		$this->db->set('id_user', $this->session->userdata('id_user'));
		$this->db->insert('plugin_sms_to_xmpp');
	}

	function delete_xmpp($uid)
	{
		$this->db->delete('plugin_sms_to_xmpp', array('id_user' => $uid));
	}
}
