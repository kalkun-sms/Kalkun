<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package     Kalkun
 * @author      Kalkun Dev Team
 * @license     http://kalkun.sourceforge.net/license.php
 * @link        http://kalkun.sourceforge.net
 */

// ------------------------------------------------------------------------

/**
 * Sms_credit_model Class
 *
 * Handle all sms credit database activity
 *
 * @package     Kalkun
 * @subpackage  Member
 * @category    Models
 */

class Sms_credit_model extends CI_Model {

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
	 * Get Users
	 *
	 * @access  public
	 * @return  object
	 */
	function get_users($param = array())
	{
		$this->db->from('plugin_sms_credit');
		$this->db->join('user', 'user.id_user = plugin_sms_credit.id_user', 'right');
		$this->db->join('plugin_sms_credit_template', 'plugin_sms_credit_template.id_credit_template = plugin_sms_credit.id_template_credit', 'left');

		if (isset($param['id']))
		{
			$this->db->where('plugin_sms_credit.id_user', $param['id']);
		}

		if (isset($param['q']))
		{
			$search_word = $this->db->escape_like_str(strtolower(str_replace("'", "''", $param['q'])));
			$this->db->like('LOWER('.$this->db->protect_identifiers('realname').')', $search_word);
		}

		if (isset($param['valid_start']) && isset($param['valid_end']))
		{
			$this->db->where('valid_start <=', $param['valid_start']);
			$this->db->where('valid_end >=', $param['valid_end']);
		}

		if (isset($param['limit']) && isset($param['offset']))
		{
			$this->db->limit($param['limit'], $param['offset']);
		}

		return $this->db->get();
	}

	// --------------------------------------------------------------------

	/**
	 * Add Users
	 *
	 * @access  public
	 * @return  object
	 */
	function add_users($param = array())
	{
		$this->load->model('User_model');

		$this->db->trans_start();

		$this->User_model->addUser();

		$param['id_user'] = $this->User_model->search_user(trim($this->input->post('realname')))->row('id_user');
		$param['id_template_credit'] = $this->input->post('package');
		$param['valid_start'] = $this->input->post('package_start');
		$param['valid_end'] = $this->input->post('package_end');
		$this->db->insert('plugin_sms_credit', $param);

		$this->db->trans_complete();
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Users
	 *
	 * @access  public
	 * @return  object
	 */
	function delete_users($id_user = NULL)
	{
		$this->load->model('User_model');

		$this->db->trans_start();
		$this->db->delete('plugin_sms_credit', array('id_user' => $id_user));
		$this->User_model->delUsers($id_user);
		$this->db->trans_complete();
	}

	// --------------------------------------------------------------------

	/**
	 * Change Users Package
	 *
	 * @access  public
	 * @return  object
	 */

	function change_users_package($param = array())
	{
		// start transcation
		$this->db->trans_start();

		$param['id_user'] = $this->input->post('id_user');
		$param['id_template_credit'] = $this->input->post('package');
		$param['valid_start'] = $this->input->post('package_start');
		$param['valid_end'] = $this->input->post('package_end');

		// Delete user package first
		$this->db->delete('plugin_sms_credit', array('id_user' => $param['id_user']));

		// insert package
		$this->db->insert('plugin_sms_credit', $param);

		$this->db->trans_complete();
	}

	// --------------------------------------------------------------------

	/**
	 * Get Packages
	 *
	 * @access  public
	 * @return  object
	 */
	function get_packages($param = array())
	{
		if (isset($param['limit']) && isset($param['offset']))
		{
			$this->db->limit($param['limit'], $param['offset']);
		}

		$this->db->from('plugin_sms_credit_template');
		return $this->db->get();
	}

	// --------------------------------------------------------------------

	/**
	 * Search Packages
	 *
	 * @access  public
	 * @return  object
	 */
	function search_packages($query = '')
	{
		$search_word = $this->db->escape_like_str(strtolower(str_replace("'", "''", $query)));
		$this->db->from('plugin_sms_credit_template');
		$this->db->like('LOWER('.$this->db->protect_identifiers('template_name').')', $search_word);
		return $this->db->get();
	}

	// --------------------------------------------------------------------

	/**
	 * Add Packages
	 *
	 * @access  public
	 * @param array
	 * @return  object
	 */
	function add_packages($param = array())
	{
		if (isset($param['id_credit_template']))
		{
			$this->db->where('id_credit_template', $param['id_credit_template']);
			unset($param['id_credit_template']);
			$this->db->update('plugin_sms_credit_template', $param);
		}
		else
		{
			$this->db->insert('plugin_sms_credit_template', $param);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Add Packages
	 *
	 * @access  public
	 * @param integer $id
	 * @return  object
	 */
	function delete_packages($id = NULL)
	{
		$this->db->trans_start();
		$this->db->delete('plugin_sms_credit', array('id_template_credit' => $id));
		$this->db->delete('plugin_sms_credit_template', array('id_credit_template' => $id));
		$this->db->trans_complete();
	}
}
