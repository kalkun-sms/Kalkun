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
 * SMS_credit_model Class
 *
 * Handle all sms credit database activity 
 *
 * @package     Kalkun
 * @subpackage  Member
 * @category    Models
 */

class SMS_credit_model extends Model {

    /**
     * Constructor
     *
     * @access	public
     */		
    function SMS_credit_model()
    {
        parent::Model();
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

        if(isset($param['id']))
        {
            $this->db->where('plugin_sms_credit.id_user', $param['id']);
        }

        if(isset($param['q']))
        {
            $this->db->like('realname', $param['q']);
        }

        if(isset($param['limit']) AND isset($param['offset']))
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
        if(isset($param['id_template_credit']))
        {
            $package['id_template_credit'] = $param['id_template_credit'];
            $package['valid_start'] = $param['valid_start'];
            $package['valid_end'] = $param['valid_end'];
            unset($param['id_template_credit']);
            unset($param['valid_start']);
            unset($param['valid_end']);
        }

        // start transcation 
        $this->db->trans_start();
        $this->db->insert('user', $param);

        // user_settings
        $user_id = $this->db->insert_id();
        $this->db->set('theme', 'blue');
        $this->db->set('signature', 'false;');
        $this->db->set('permanent_delete', 'false');
        $this->db->set('paging', '20');
        $this->db->set('bg_image', 'true;background.jpg');
        $this->db->set('delivery_report', 'default');
        $this->db->set('language', 'english');	
        $this->db->set('conversation_sort', 'asc');
        $this->db->set('id_user', $user_id);
        $this->db->insert('user_settings');

        // packages
        $package['id_user'] = $user_id;
        $this->db->insert('plugin_sms_credit', $package);

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
        $this->db->trans_start();
        $this->db->delete('plugin_sms_credit', array('id_user' => $id_user));
        $this->db->delete('sms_used', array('id_user' => $id_user));
        $this->db->delete('user_folders', array('id_user' => $id_user));
        $this->db->delete('pbk', array('id_user' => $id_user));
        $this->db->delete('pbk_groups', array('id_user' => $id_user));
        $this->db->delete('user_settings', array('id_user' => $id_user));
        $this->db->delete('user', array('id_user' => $id_user));
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
        if(isset($param['limit']) AND isset($param['offset']))
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
        $this->db->from('plugin_sms_credit_template');
        $this->db->like('template_name', $query);
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
        if(isset($param['id_credit_template']))
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

/* End of file sms_credit_model.php */
/* Location: ./application/plugins/sms_credit/models/sms_credit_model.php */
