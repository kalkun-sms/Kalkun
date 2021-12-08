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
 * Sms_to_wordpress_model Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Models
 */
class Sms_to_wordpress_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
	}
	
	function check_status($uid)
	{
		$exist = FALSE;
		$this->db->from('plugin_sms_to_wordpress');
		$this->db->where('id_user', $uid);
		
		if ($this->db->count_all_results() === 1)
		{
			$exist = TRUE;
		}
		return $exist;
	}
	
	function get_wp($uid)
	{
		$secret = FALSE;
		$this->db->from('plugin_sms_to_wordpress');
		$this->db->where('id_user', $uid);
		$user_wp = $this->db->get();
		if ($user_wp->num_rows() === 1)
		{
			$secret['wp_username'] = $user_wp->row('wp_username');
			$secret['wp_password'] = $user_wp->row('wp_password');
			$secret['wp_url'] = $user_wp->row('wp_url');
		}
		return $secret;
	}
	
	function get_wp_url_by_phone($number)
	{
		$secret = FALSE;
		$this->db->from('user');
		$this->db->where('phone_number', $number);
		$user = $this->db->get();
		if ($user->num_rows() === 1)
		{
			$secret = $this->get_wp($user->row('id_user'));
		}
		return $secret;
	}
	
	function save_wp()
	{
		$this->load->library('encryption');
		$encrypted_pwd = $this->encryption->encrypt($this->input->post('wp_password'));
		if ($encrypted_pwd === FALSE)
		{
			log_message('error', 'sms_to_wordpress: problem during encryption.');
			show_error('sms_to_wordpress: problem during encryption.', 500, '500 Internal Server Error');
		}
		$this->db->set('wp_username', $this->input->post('wp_username'));
		$this->db->set('wp_password', $encrypted_pwd);
		$this->db->set('wp_url', $this->input->post('wp_url'));
		$this->db->set('id_user', $this->session->userdata('id_user'));
		$this->db->insert('plugin_sms_to_wordpress');
	}
	
	function delete_wp($uid)
	{
		$this->db->delete('plugin_sms_to_wordpress', array('id_user' => $uid)); 
	}
	
}

