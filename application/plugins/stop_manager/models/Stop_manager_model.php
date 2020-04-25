<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package Kalkun
 * @author  Kalkun Dev Team
 * @license http://kalkun.sourceforge.net/license.php
 * @link    http://kalkun.sourceforge.net
 */

// ------------------------------------------------------------------------

/**
 * Stop_manager_model Class
 *
 * Handle all plugin database activity
 *
 * @package     Kalkun
 * @subpackage  Plugin
 * @category    Models
 */
class Stop_manager_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    function get($option=NULL, $limit=NULL, $offset=NULL)
    {
        switch($option)
        {
            case 'all':
                return $this->db->get('plugin_stop_manager');
            break;

            case 'paginate':
                return $this->db->get('plugin_stop_manager', $limit, $offset);
            break;

            case 'count':
                $this->db->select('count(*) as count');
                return $this->db->get('plugin_stop_manager')->row('count');
            break;
        }
    }

    function get_num_for_type($type)
    {
        return $this->db->query("select distinct(destination_number) from plugin_stop_manager where stop_type ilike '$type'");
    }

    function add($number, $type, $msg)
    {
        $data = array (
                'destination_number' => trim($number),
                'stop_type' => $type,
                'stop_message' => $msg,
                'reg_date' => date ('Y-m-d H:i:s'),
                    );
        $this->db->insert('plugin_stop_manager',$data);
    }

    function delete($number, $type)
    {
        $this->db->delete('plugin_stop_manager', array('destination_number' => trim($number), 'stop_type' => $type));
    }
}

/* End of file Stop_manager_model.php */
/* Location: ./application/plugins/stop_manager/models/Stop_manager_model.php */
