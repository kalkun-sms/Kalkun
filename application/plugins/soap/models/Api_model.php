<?php
/**
 *	@Author: bullshit "oskar@biglan.at"
 *	@Copyright: bullshit, 2010
 *	@License: GNU General Public License
*/

Class Api_model extends CI_Model {

	function Plugin_model()
	{
		$this->load->library('Remote_Messages');
	}

	function getAccount($token)
	{
		$sql = "select ip_address,id_remote_access,status from plugin_remote_access where token='".$token."'";
		$remote_ip = $this->db->query($sql)->row('ip_address');
		$remote_Id = $this->db->query($sql)->row('id_remote_access');
		$status = ($this->db->query($sql)->row('status') === 'false') ? FALSE : TRUE;
		return array ('ip' => $remote_ip, 'id' => $remote_Id, 'status' => $status);
	}
}
