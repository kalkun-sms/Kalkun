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
 * Phonebook_model Class
 *
 * Handle all phonebook database activity 
 *
 * @package		Kalkun
 * @subpackage	Phonebook
 * @category	Models
 */
class Phonebook_model extends Model {
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */	
	function Phonebook_model()
	{
		parent::Model();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Get Phonebook
	 *
	 * @access	public   		 
	 * @param	mixed $param
	 * @return	object
	 */	
	function get_phonebook($param)
	{
	   
	   if( isset($param['id_user']) && !empty($param['id_user']) ) $user_id = $param['id_user'];
       else $user_id = $this->session->userdata('id_user') ; 
       
		switch($param['option']) 
		{
			case 'all':
			$this->db->select('*');
			$this->db->select_as('pbk.ID','id_pbk');
			$this->db->select_as('pbk_groups.Name', 'GroupName');	
			$this->db->from('pbk');
			$this->db->where('pbk.id_user', $user_id);
            $this->db->join('user_group', 'user_group.id_pbk=pbk.ID', 'left');
			$this->db->join('pbk_groups', 'pbk_groups.ID=user_group.id_pbk_groups', 'left');
			$this->db->order_by('pbk.Name');
			break;	
			
			case 'paginate':
			$this->db->select('*');
			$this->db->select_as('ID', 'id_pbk');	
			$this->db->from('pbk');
			if(isset($param['public']) && $param['public']) $this->db->where('is_public', 'true');
			else $this->db->where('id_user',$user_id);
			$this->db->order_by('Name');
			$this->db->limit($param['limit'], $param['offset']);
			break;
			
			case 'by_idpbk':
			$this->db->select('pbk.*');
			$this->db->select_as('pbk.ID','id_pbk');
            $this->db->select_as('pbk.Name', 'Name');	
			$this->db->select_as('pbk_groups.Name', 'GroupName');	
			$this->db->from('pbk');
			$this->db->where('pbk.id_user', $user_id);
            $this->db->join('user_group', 'user_group.id_pbk=pbk.ID', 'left');
			$this->db->join('pbk_groups', 'pbk_groups.ID=user_group.id_pbk_groups', 'left');
			$this->db->where('pbk.ID', $param['id_pbk']);
			break;
			
			case 'group':
			$this->db->select('*');
			$this->db->select_as('Name','GroupName');
			$this->db->from('pbk_groups');
			if(isset($param['public']) && $param['public']) $this->db->where('is_public', 'true');
			else $this->db->where('id_user',$user_id);			
			$this->db->order_by('Name');
			break;
		
			case 'group_paginate':
			$this->db->select('*');
			$this->db->select_as('Name', 'GroupName');
			$this->db->from('pbk_groups');
			if(isset($param['public']) && $param['public']) $this->db->where('is_public', 'true');
			else $this->db->where('id_user',$user_id);
			$this->db->order_by('Name');
			$this->db->limit($param['limit'], $param['offset']);
			break;	
			
			case 'groupname':
			$this->db->select('ID');
			$this->db->select_as('Name', 'GroupName');
			$this->db->from('pbk_groups');
            $condition = "({$this->_protect_identifiers('id_user')} = {$user_id} OR {$this->_protect_identifiers('is_public')} = 'true')";            
            $this->db->where($condition, NULL, FALSE);
            $this->db->where('ID', $param['id']);
			break;
			
			case 'bynumber':
			// search phone number prefix
			$arr_number = $this->convert_phonenumber(array('number' => $param['number'], 'id_user' => $user_id));

			$this->db->select('*');
			$this->db->select_as('ID', 'id_pbk');	
			$this->db->from('pbk');
			$this->db->where("({$this->_protect_identifiers('id_user')} = '$user_id' OR {$this->_protect_identifiers('is_public')} = 'true')");
			$this->db->where_in('Number', $arr_number);
            break;
			
			case 'bygroup':
            $this->db->select('*');	
			$this->db->from('pbk');
            $this->db->select_as('pbk.Name', 'Name');	
            $this->db->select_as('pbk_groups.Name', 'GroupName');
            $this->db->join('user_group', 'user_group.id_pbk=pbk.ID');
			$this->db->join('pbk_groups', 'pbk_groups.ID=user_group.id_pbk_groups');
            $condition = "({$this->_protect_identifiers('pbk_groups.id_user')} = {$user_id} OR {$this->_protect_identifiers('pbk_groups.is_public')} = 'true')";
            $this->db->where($condition, NULL, FALSE);
            $this->db->where('user_group.id_pbk_groups', $param['group_id']);
            $this->db->order_by("pbk.Name", "asc");
            
            if(isset($param['limit']) && isset($param['offset'])) $this->db->limit($param['limit'], $param['offset']);
			break;
			
			case 'search':
			$this->db->select('*');
			$this->db->select_as('ID', 'id_pbk');
			$this->db->from('pbk');
			$condition1 = "({$this->_protect_identifiers('id_user')} = {$user_id} OR {$this->_protect_identifiers('is_public')} = 'true')";
			$condition2 = "({$this->_protect_identifiers('Name')} LIKE '%{$this->input->post('search_name')}%' OR {$this->_protect_identifiers('Number')} LIKE '%{$this->input->post('search_name')}%')";
			$this->db->where($condition1, NULL, FALSE);
			$this->db->where($condition2, NULL, FALSE);
			$this->db->order_by('Name');
			break;
			
			case 'public':
			$this->db->select('*');
			$this->db->select_as('pbk.ID','id_pbk');
			$this->db->select_as('pbk_groups.Name', 'GroupName');	
			$this->db->from('pbk');
			$this->db->where('pbk.is_public', 'true');
            $this->db->join('user_group', 'user_group.id_pbk=pbk.ID', 'left');
			$this->db->join('pbk_groups', 'pbk_groups.ID=user_group.id_pbk_groups', 'left');
			$this->db->order_by('pbk.Name');
			break;
		}
		//echo $this->db->last_query();
		return $this->db->get();	
	}
	// --------------------------------------------------------------------
	
	/**
	* _protect_identifiers
	*
	* Ugly hack to add backticks to database field
	*
	* @param string $identifier
	* @return string
	*/
	function _protect_identifiers($identifier=NULL)
	{
		$this->load->helper('kalkun');
		$escape_char;
		$escaped_identifer="";
		
		// get database engine
		$db_engine = $this->db->platform();
		$escape_char = get_database_property($db_engine);
		$escape_char = $escape_char['escape_char'];
		
		$sub = explode(".", $identifier);
		$sub_count = count($sub);

		foreach($sub as $key => $tmp)
		{
			$escaped_identifer.=$escape_char.$tmp.$escape_char;
			
			// if this is not the last
			if($key!=$sub_count-1)
			{
				$escaped_identifer.=".";
			}
		}
		
	    return $escaped_identifer;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Search Phonebook
	 *
	 * @access	public   		 
	 * @param	mixed $param
	 * @return	object
	 */		
	function search_phonebook($param)	
	{
		$this->db->from('pbk');
		$this->db->select_as('Number', 'id');
		$this->db->select_as('Name', 'name');
		$this->db->where("({$this->_protect_identifiers('id_user')} = '{$param['uid']}'  OR {$this->_protect_identifiers('is_public')} = 'true' )");
		$this->db->like('Name', $param['query']);
		$this->db->order_by('Name');		
		return $this->db->get();
	}

	// --------------------------------------------------------------------
	
	/**
	 * Search Group
	 *
	 * @access	public   		 
	 * @param	mixed $param
	 * @return	object
	 */		
	function search_group($param)	
	{
		$this->db->from('pbk_groups');
		$this->db->select_as('ID', 'id');
		$this->db->select_as('Name', 'name');
		$this->db->where("({$this->_protect_identifiers('pbk_groups')}.{$this->_protect_identifiers('id_user')} = '{$param['uid']}'  OR {$this->_protect_identifiers('is_public')} = 'true' )");
		$this->db->like('Name', $param['query']);
		$this->db->order_by('Name');
		$this->db->join('user_group', 'user_group.id_pbk_groups=pbk_groups.ID');
		$this->db->group_by('Name');
		$this->db->group_by('ID');
		return $this->db->get();
	}	

	// --------------------------------------------------------------------
	
	/**
	 * Add Contact
	 *
	 * @access	public   		 
	 * @param	mixed $param
	 * @return
	 */		
	function add_contact($param)
	{
        $param['Number'] = str_replace(' ', '', $param['Number']);
        $param['Number'] = str_replace('-', '', $param['Number']);
		$this->db->set('Name', $param['Name']);
		$this->db->set('Number', $param['Number']);
		$this->db->set('id_user', $param['id_user']);
		$this->db->set('is_public', $param['is_public']);
		
		// edit mode
		if(isset($param['id_pbk'])) 
		{
			$this->db->where('ID', $param['id_pbk']);
			$this->db->update('pbk');
		}
		else $this->db->insert('pbk');
        
        // optimisation required.
        if(isset($param['id_pbk'])) 
		{
            $pbk_id = $param['id_pbk'];
        }
        else $pbk_id = $this->db->insert_id();
        
        //delete past groups
        $this->db->delete('user_group', array('id_pbk' => $pbk_id)); 
        
        // now insert the lastest
        if(isset($param['GroupID']))
            if(!empty($param['GroupID']))
            {
                $this->db->set('id_pbk', $pbk_id);
        		$this->db->set('id_pbk_groups', $param['GroupID']);
        		$this->db->set('id_user', $param['id_user']);
                $this->db->insert('user_group');
            }
        if(isset($param['Groups']))
        if(!empty($param['Groups'])){
            $groups = array_unique(explode(',',$param['Groups']));
            $CI =& get_instance();
            foreach($groups as $_grp)
            {   
                $group_id  = $CI->Phonebook_model->group_id($_grp,$param['id_user']);
                
                if($group_id != null)
                {
                    $this->db->set('id_pbk', $pbk_id);
            		$this->db->set('id_pbk_groups', $group_id);
            		$this->db->set('id_user', $param['id_user']);
                    $this->db->insert('user_group');
                }
            }
             
            
        }
        
	}
  
  function multi_attach_group()
  {
     
     $id_group = $this->input->post('id_group');
     $id_pbk = $this->input->post('id_pbk');
     
     if($id_group == 'null' ) die("Invalid Group ID");
     
     //parse group value
     if(preg_match('/-/',$id_group)) { $mode = 'delete'; $id_group = substr($id_group,1);  }
     else $mode = 'add';
     
     if($mode == 'delete')
     {
        $this->db->delete('user_group', array('id_pbk' => $id_pbk , 'id_pbk_groups' => $id_group)); 
     }
     else // Add Mode 
     {
        $this->db->from('user_group');
        $this->db->where('id_pbk', $id_pbk);
		    $this->db->where('id_pbk_groups', $id_group);
        
        if($this->db->get()->num_rows() < 1)
        {
           $this->db->set('id_pbk', $id_pbk);
           $this->db->set('id_pbk_groups', $id_group);
           $this->db->set('id_user',  $this->session->userdata('id_user'));
           $this->db->insert('user_group');
        }

     }
     
  }

	// --------------------------------------------------------------------
	
	/**
	 * Add Group
	 *
	 * @access	public   		 
	 * @param	mixed $param
	 * @return
	 */		
	function add_group()
	{
		$this->db->set('Name', trim($this->input->post('group_name')));
		$this->db->set('id_user', trim($this->input->post('pbkgroup_id_user')));
		$this->db->set('is_public', $this->input->post('is_public')? 'true' : 'false');
			
		// edit mode	
		if($this->input->post('pbkgroup_id'))
		{
			$this->db->where('ID', $this->input->post('pbkgroup_id'));
			$this->db->update('pbk_groups');
		}
		else $this->db->insert('pbk_groups');		
	}	
    
    // --------------------------------------------------------------------
    
     /**
	 * Get Groups ID for a Group Name
	 *
	 * @access	public   		 
	 * @param	text $group_name
     * @param	number $user_id
	 * @return
	 */	
    function group_id($group_name, $user_id)
    {
        	$this->db->select('*');
			$this->db->from('pbk_groups');
            $this->db->where('Name', $group_name);
			$this->db->where('id_user', $user_id);
            return @$this->db->get()->row()->ID;
    }
    
    // --------------------------------------------------------------------
    
     /**
	 * Get Groups Name for a Group ID
	 *
	 * @access	public   		 
	 * @param	string $group_name
     * @param	number $user_id
	 * @return
	 */	
    function group_name($group_id, $user_id)
    {
       	$this->db->select('*');
		$this->db->from('pbk_groups');
        $this->db->where('ID', $group_id);
		$this->db->where('id_user', $user_id);
        return @$this->db->get()->row()->Name;
    }
    
    // --------------------------------------------------------------------
    
    /**
	 * Get Groups for  a contact id
	 *
	 * @access	public   		 
	 * @param	number $pbk_id
     * @param	number $user_id
	 * @return
	 */	
    function get_groups($pbk_id,$user_id)
    {
        $this->db->select_as('user_group.id_pbk_groups', 'GroupID');
        $this->db->select_as('pbk_groups.Name', 'GroupName');	
		$this->db->from('user_group');
        $this->db->join('pbk_groups', 'pbk_groups.ID=user_group.id_pbk_groups');           
        $this->db->where('user_group.id_user', $user_id);
		$this->db->where('user_group.id_pbk', $pbk_id);
        $q =  $this->db->get();
        $GroupID = $GroupName = '';
        foreach ($q->result() as $_gp) 
        {
            $GroupName .= $_gp->GroupName.',';
            $GroupID .= $_gp->GroupID .',';
        }
        $GroupName = substr($GroupName,0, strlen($GroupName)-1);
        $GroupID = substr($GroupID,0, strlen($GroupID)-1);
        return (object) array("GroupNames" => $GroupName, "GroupIDs" => $GroupID);
    }

	// --------------------------------------------------------------------
	
	/**
	 * Delete Contact
	 *
	 * @access	public   		 
	 * @param	number $id_contact
	 * @return
	 */		
	function delete_contact()
	{
		$this->db->delete('pbk', array('ID' => $this->input->post('id'))); 
        $this->db->delete('user_group', array('id_pbk' => $this->input->post('id'))); 
	}

	// --------------------------------------------------------------------
	
	/**
	 * Delete Group
	 *
	 * @access	public   		 
	 * @param	mixed $id_group
	 * @return
	 */	
	function delete_group()
	{
		$this->db->delete('pbk', array('GroupID' => $this->input->post('id'))); 
		$this->db->delete('pbk_groups', array('ID' => $this->input->post('id'))); 
        $this->db->delete('user_group', array('id_pbk_groups' => $this->input->post('id'))); 
	}

	// --------------------------------------------------------------------
	
	/**
	 * Get Phonenumber (original, localization, and internationalization )
	 *
	 * @access	public   		 
	 * @param	array $param
	 * @return array
	 */	
	function convert_phonenumber($param)
	{
		if(!isset($param['id_user'])) $param['id_user'] = '';
		$this->load->helper('country_dial_code_helper');
		$country_code = $this->Kalkun_model->get_setting($param['id_user'])->row('country_code');
		$dial_code = getCountryInformation($country_code);
		$dial_code = '+'.$dial_code['dial_code'];
		$number_local = str_replace($dial_code, '0', $param['number']);
		$number_inter = $dial_code.substr($param['number'], 1);
		return array($param['number'], $number_local, $number_inter);	
	}
}

/* End of file phonebook_model.php */
/* Location: ./application/models/phonebook_model.php */
