<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-3.0-or-later.html
 * @link		https://kalkun.sourceforge.io/
 */

// ------------------------------------------------------------------------

/**
 * User_model Class
 *
 * Handle all user database activity
 *
 * @package		Kalkun
 * @subpackage	User
 * @category	Models
 */
class User_model extends MY_Model {

	// --------------------------------------------------------------------

	/**
	 * Get User
	 *
	 * @access	public
	 * @param	mixed $param
	 * @return	object
	 */
	function getUsers($param)
	{
		$this->db->from('user_settings');
		$this->db->join('user', 'user.id_user = user_settings.id_user');
		switch ($param['option'])
		{
			case 'all':
				$this->db->select('*');
				break;

			case 'paginate':
				$this->db->limit($param['limit'], $param['offset']);
				break;

			case 'by_iduser':
				$this->db->where('user.id_user', $param['id_user']);
				break;

			case 'search':
				$search_word = $this->db->escape_like_str(strtolower(str_replace("'", "''", $this->input->post('search_name'))));
				$this->db->like('LOWER('.$this->db->protect_identifiers('realname').')', $search_word);
				break;
		}
		$this->db->order_by('realname');
		return $this->db->get();
	}

	// --------------------------------------------------------------------

	/**
	 * Add User
	 *
	 * @access	public
	 * @param	mixed
	 * @return
	 */
	function addUser()
	{
		$this->load->helper('kalkun');
		$this->db->set('realname', trim($this->input->post('realname')));
		$this->db->set('username', trim($this->input->post('username')));
		$this->_phone_number_validation($this->input->post('phone_number'));
		$this->db->set('phone_number', phone_format_e164($this->input->post('phone_number')));
		$this->db->set('level', $this->input->post('level'));

		// edit mode
		if ($this->input->post('id_user'))
		{
			$this->db->where('id_user', $this->input->post('id_user'));
			$this->db->update('user');
		}
		else
		{
			$this->db->set('password', password_hash($this->input->post('password'), PASSWORD_BCRYPT));
			$this->db->insert('user');

			// user_settings
			$this->db->set('theme', 'blue');
			$this->db->set('signature', 'false;');
			$this->db->set('permanent_delete', 'false');
			$this->db->set('paging', '20');
			$this->db->set('bg_image', 'true;background.jpg');
			$this->db->set('delivery_report', 'default');
			$this->db->set('language', 'english');
			$this->db->set('conversation_sort', 'asc');
			$this->db->set('id_user', $this->db->insert_id());

			$this->db->insert('user_settings');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Delete User
	 *
	 * @access	public
	 * @param	number $id_user ID user to delete
	 * @return
	 */
	function delUsers($id_user)
	{
		$this->db->delete('sms_used', array('id_user' => $id_user));
		$this->db->delete('user_folders', array('id_user' => $id_user));
		$this->db->delete('pbk', array('id_user' => $id_user));
		$this->db->delete('pbk_groups', array('id_user' => $id_user));
		$this->db->delete('user_settings', array('id_user' => $id_user));
		$this->db->delete('user', array('id_user' => $id_user));
	}

	// --------------------------------------------------------------------

	/**
	 * Search User
	 *
	 * @access	public
	 * @param	string $realname
	 * @return	object
	 */
	function search_user($realname)
	{
		$search_word = $this->db->escape_like_str(strtolower(str_replace("'", "''", $realname)));
		$this->db->from('user_settings');
		$this->db->join('user', 'user.id_user = user_settings.id_user');
		$this->db->like('LOWER('.$this->db->protect_identifiers('realname').')', $search_word);
		$this->db->order_by('realname');
		return $this->db->get();
	}

	// --------------------------------------------------------------------

	/**
	 * Check if submitted phone number is valid
	 *
	 * @access	public
	 */
	function _phone_number_validation($phone)
	{
		$this->load->helper('kalkun');
		$result = is_phone_number_valid($phone);

		if ($result !== TRUE)
		{
			show_error($result, 400);
		}
	}
}
