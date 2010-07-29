<?php
Class Kalkun_model extends Model {
	
	function Kalkun_model()
	{
		parent::Model();
	}
	
	//=================================================================
	// LOGIN
	//=================================================================
	
	function Login()
	{
		$username = $this->input->post('username');
		$password = sha1($this->input->post('password'));
		$sql = "select * from user where username='".$username."' and password='".$password."'";
		$query = $this->db->query($sql);
		if($query->num_rows()=='1') {
			$this->session->set_userdata('loggedin', 'TRUE');
			$this->session->set_userdata('level', $query->row('level'));
			$this->session->set_userdata('id_user', $query->row('id_user'));
			$this->session->set_userdata('username', $query->row('username'));			
			redirect('kalkun');
		}
		else $this->session->set_flashdata('errorlogin', 'Your username or password are incorrect');
	}	

	//=================================================================
	// FOLDER
	//=================================================================

	function getFolders($option=NULL, $id_folder=NULL, $id_user=NULL)
	{
		switch($option)
		{
			case 'all':
			$sql = "select * from user_folders where id_folder > 5";
			break;
			
			case 'exclude':
			$sql = "select * from user_folders where id_folder > 5 and id_folder!='".$id_folder."'";
			break;
			
			case 'name':
			$sql = "select * from user_folders where id_folder='".$id_folder."'";
			break;
			
		}
		
		if($id_folder!='5') $sql.= " and id_user='".$this->session->userdata('id_user')."'";
		$sql .= " order by name";
		return $this->db->query($sql);	
	}
	
	function addFolder()
	{
		$data = array (
				'name' => $this->input->post('folder_name'),
				'id_user' => $this->input->post('id_user')
					);
		$this->db->insert('user_folders',$data);		
	}
		
	function renameFolder()
	{
		$sql = "update user_folders set name='".$this->input->post('edit_folder_name')."' where id_folder='".$this->input->post('id_folder')."'";
		$this->db->query($sql);
	}
	
	function deleteFolder($id_folder=NULL)
	{
		$id_user = $this->session->userdata('id_user');
				
		// inbox
		$inbox = "DELETE i, ui
				FROM user_folders AS uf
				LEFT JOIN inbox AS i ON i.id_folder = uf.id_folder
				LEFT JOIN user_inbox AS ui ON ui.id_inbox = i.ID
				WHERE uf.id_folder = '".$id_folder."'";
		$this->db->query($inbox);
		
		// Sentitems
		$sentitems = "DELETE s, us
				FROM user_folders AS uf
				LEFT JOIN sentitems AS s ON s.id_folder = uf.id_folder
				LEFT JOIN user_sentitems AS us ON us.id_sentitems = s.ID
				WHERE uf.id_folder = '".$id_folder."'";
		$this->db->query($sentitems);	
		
		$this->db->delete('user_folders', array('id_folder' => $id_folder, 'id_user' => $id_user)); 
	}
	
	
	//=================================================================
	// SETTINGS
	//=================================================================	

	function UpdateSetting($option)
	{
		switch($option)
		{
			case 'general':
				$this->db->set('language', $this->input->post('language'));
				$this->db->set('paging', $this->input->post('paging'));
				$this->db->set('permanent_delete', $this->input->post('permanent_delete'));
				$this->db->set('delivery_report', $this->input->post('delivery_report'));
				$this->db->set('conversation_sort', $this->input->post('conversation_sort'));
				$this->db->where('id_user', $this->session->userdata('id_user'));
				$this->db->update('user_settings');
			break;
			
			case 'personal':
				$this->db->set('realname', $this->input->post('realname'));
				$this->db->set('username', $this->input->post('username'));
				$this->db->set('phone_number', $this->input->post('phone_number'));
				$this->db->where('id_user', $this->session->userdata('id_user'));
				$this->db->update('user');
				
				$sig_opt = $this->input->post('signatureoption');
				$this->db->set('signature', $sig_opt.';'.$this->input->post('signature'));
				$this->db->where('id_user', $this->session->userdata('id_user'));
				$this->db->update('user_settings');
			break;
			
			case 'appearance':
				$this->db->set('theme', $this->input->post('theme'));
				$this->db->set('bg_image', $this->input->post('bg_image_option').';background.jpg');
				$this->db->where('id_user', $this->session->userdata('id_user'));
				$this->db->update('user_settings');
			break;
			
			case 'password':
				$this->db->set('password', sha1($this->input->post('new_password')));
				$this->db->where('id_user', $this->session->userdata('id_user'));
				$this->db->update('user');				
			break;
		}		
	}
	
	function getSetting()
	{
		$id_user = $this->session->userdata('id_user');
		$this->db->where('user.id_user', $id_user);
		$this->db->join('user', 'user.id_user = user_settings.id_user');
		return $this->db->get('user_settings');
	}
	
	// check duplicate entry
	function checkSetting($param)
	{
		$this->db->from('user');
		switch($param['option'])
		{
			case 'username':
			$this->db->where('username', $param['username']);
			break;	
			
			case 'phone_number':
			$this->db->where('phone_number', $param['phone_number']);
			break;				
		}
		return $this->db->get();
		
	}
	
	
	//=================================================================
	// GAMMU INFO
	//=================================================================	
	
	function getGammuInfo($option)
	{
		switch($option)
		{
			case 'gammu_version':
				$sql = "select Client from phones order by UpdatedInDB desc limit 1";
				break;
			case 'db_version':
				$sql = "select Version from gammu";
				break;	
			case 'last_activity':
				$sql = "select UpdatedInDB from phones order by UpdatedInDB desc limit 1";
				break;
			case 'phone_imei':
				$sql = "select IMEI from phones order by UpdatedInDB desc limit 1";
				break;	
			case 'phone_signal':
				$sql = "select Signal from phones order by UpdatedInDB desc limit 1";
				break;	
			case 'phone_battery':
				$sql = "select Battery from phones order by UpdatedInDB desc limit 1";
				break;															
		}
		return $this->db->query($sql);
	}
	
	
	//=================================================================
	// MISC
	//=================================================================		
	
	function getInfo($option)
	{
		switch($option)
		{
			case 'database_used':
				$dbsize = 0;
				$sql = "SHOW TABLE STATUS";
				$tmp_res = $this->db->query($sql);
				foreach($tmp_res->result() as $tmp):
				$dbsize += $tmp->Data_length + $tmp->Index_length;
				endforeach;
				return $dbsize;
			break;	
		}
	}
	
	
	//=================================================================
	// SMS USED
	//=================================================================		
	
	function get_sms_used($option, $param)
	{
		switch($option)
		{
			case 'date':
				$this->db->select('sms_count');
				$this->db->from('sms_used');
				$this->db->where('sms_date', $param['sms_date']);
				$this->db->where('id_user', $param['user_id']);
				$res = $this->db->get()->row('sms_count');
				
				if(!$res) return 0;
				else return $res;
			break;	
		}
	}
	
	function add_sms_used($user_id)
	{
		$date = date("Y-m-d");
		$count = $this->_check_sms_used($date, $user_id);
		if($count>0)
		{	
			$this->db->set('sms_count', $count+1);
			$this->db->where('sms_date', $date);
			$this->db->where('id_user', $user_id);
			$this->db->update('sms_used');
		}	
		else
		{
			$this->db->set('sms_count', '1');
			$this->db->set('sms_date', $date);
			$this->db->set('id_user', $user_id);
			$this->db->insert('sms_used'); 
		}
	}
	
	function _check_sms_used($date, $user_id)
	{
		$this->db->select("sms_count");
		$this->db->from('sms_used');
		$this->db->where('sms_date', $date);
		$this->db->where('id_user', $user_id);
		$res = $this->db->get()->row('sms_count');
		if(!$res) return 0;
		else return $res;
	}
}
?>
