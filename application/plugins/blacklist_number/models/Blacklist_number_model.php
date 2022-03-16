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
 * Blacklist_number_model Class
 *
 * Handle all plugin database activity
 *
 * @package		Kalkun
 * @subpackage	Plugin
 * @category	Models
 */
class Blacklist_number_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
		$this->load->helper('kalkun');
	}

	function get($option = NULL, $limit = NULL, $offset = NULL)
	{
		switch ($option)
		{
			case 'all':
				return $this->db->get('plugin_blacklist_number');
			break;

			case 'paginate':
				return $this->db->get('plugin_blacklist_number', $limit, $offset);
			break;

			case 'count':
				$this->db->select('count(*) as count');
				return $this->db->get('plugin_blacklist_number')->row('count');
			break;
		}
	}

	function add()
	{
		$data = array (
			'phone_number' => trim(phone_format_e164($this->input->post('phone_number'))),
			'reason' => trim($this->input->post('reason')),
		);
		$this->db->insert('plugin_blacklist_number', $data);
	}

	function update()
	{
		$data = array (
			'phone_number' => trim(phone_format_e164($this->input->post('editphone_number'))),
			'reason' => $this->input->post('editreason'),
		);
		$this->db->where('id_blacklist_number', $this->input->post('editid_blacklist_number'));
		$this->db->update('plugin_blacklist_number', $data);
	}

	function delete($id)
	{
		$this->db->delete('plugin_blacklist_number', array('id_blacklist_number' => $id));
	}
}
