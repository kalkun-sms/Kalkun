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
    function get_users()
    {
        $this->db->from('plugin_sms_credit');
        $this->db->join('user', 'user.id_user = plugin_sms_credit.id_user', 'right');
        $this->db->join('plugin_sms_credit_template', 'plugin_sms_credit_template.id_credit_template = plugin_sms_credit.id_template_credit', 'left');
        return $this->db->get();
    }

    // --------------------------------------------------------------------

    /**
     * Get Packages
     *
     * @access  public
     * @return  object
     */
    function get_packages()
    {
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


}

/* End of file sms_credit_model.php */
/* Location: ./application/plugins/sms_credit/models/sms_credit_model.php */
