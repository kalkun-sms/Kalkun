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
 * SMS_to_twitter_model Class
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Models
 */
class SMS_to_twitter_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
	}
	
	function check_token($uid)
	{
		$exist = FALSE;
		$this->db->from('plugin_sms_to_twitter');
		$this->db->where('id_user', $uid);
		
		if ($this->db->count_all_results() == 1)
		{
			$exist = TRUE;
		}
		return $exist;
	}
	
	function save_token($param = array())
	{
		$this->db->set($param);
		$this->db->insert('plugin_sms_to_twitter');
	}
	
	function delete_token($uid)
	{
		$this->db->delete('plugin_sms_to_twitter', array('id_user' => $uid)); 
	}
	
	function get_token_by_phone($number)
	{
		$tokens = FALSE;
		$this->db->from('user');
		$this->db->where('phone_number', $number);
		$user = $this->db->get();
		if ($user->num_rows() == 1)
		{
			$uid = $user->row('id_user');
			$this->db->from('plugin_sms_to_twitter');
			$this->db->where('id_user', $uid);
			$user_token = $this->db->get();
			if ($user_token->num_rows() == 1)
			{
				$tokens['access_token'] = $user_token->row('access_token');
				$tokens['access_token_secret'] = $user_token->row('access_token_secret');
			}
		}
		return $tokens;
	}
}

/* End of file sms_to_twitter_model.php */
/* Location: ./application/plugins/sms_to_twitter/models/sms_to_twitter_model.php */