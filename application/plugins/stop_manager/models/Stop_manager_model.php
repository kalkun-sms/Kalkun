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
                $this->db->select('*');
                $this->db->order_by('reg_date', 'DESC');
                return $this->db->get('plugin_stop_manager');
            break;

            case 'paginate':
                $this->db->select('*');
                $this->db->order_by('reg_date', 'DESC');
                return $this->db->get('plugin_stop_manager', $limit, $offset);
            break;

            case 'search':
                $search_word = $this->db->escape_like_str(strtolower(str_replace("'", "''", $this->input->post('search_name'))));
                $this->db->select('*');
                $this->db->from('plugin_stop_manager');
                $this->db->like('LOWER('.$this->db->protect_identifiers('destination_number').')', $search_word);
                $this->db->or_like('LOWER('.$this->db->protect_identifiers('stop_type').')', $search_word);
                $this->db->or_like('LOWER('.$this->db->protect_identifiers('stop_message').')', $search_word);
                $this->db->order_by('reg_date', 'DESC');
                return $this->db->get();
            break;

            case 'count':
                $this->db->select('count(*) as count');
                return $this->db->get('plugin_stop_manager')->row('count');
            break;
        }
    }

    function get_num_for_type($type)
    {
        // Query is:
        // SELECT DISTINCT(destination_number)
        // FROM plugin_stop_manager
        // WHERE LOWER("stop_type") = LOWER('$type')"

        $this->db->distinct();
        $this->db->select('destination_number');
        $this->db->where('LOWER('.$this->db->protect_identifiers('stop_type').')', strtolower(str_replace("'", "''", $type)));
        return $this->db->get('plugin_stop_manager');
    }

    function add($number, $type, $msg)
    {
        $this->db->where('destination_number', trim($number));
        $this->db->where('stop_type', trim($type));
        $q = $this->db->get('plugin_stop_manager');
        $this->db->reset_query();

        if ($q->num_rows() == 1)
        {
            // do UPDATE (there is already a row)
            $data = array (
                'stop_message' => trim($msg),
                'reg_date' => date ('Y-m-d H:i:s'),
            );
            $this->db->where('destination_number', trim($number));
            $this->db->where('stop_type', trim($type));
            $this->db->update('plugin_stop_manager', $data);
        }
        else
        {
            if ( $q->num_rows() > 1 )
            {
                // do DELETE (there is more than 1 row, so remove them)
                $this->delete($number, $type);
            }

            // do INSERT (there is no record for this (number;type))
            $data = array (
                    'destination_number' => trim($number),
                    'stop_type' => trim($type),
                    'stop_message' => trim($msg),
                    'reg_date' => date ('Y-m-d H:i:s'),
            );
            $this->db->insert('plugin_stop_manager',$data);
        }
    }

    function delete($number, $type)
    {
        $this->db->delete('plugin_stop_manager', array('destination_number' => trim($number), 'stop_type' => trim($type)));
    }
}

/* End of file Stop_manager_model.php */
/* Location: ./application/plugins/stop_manager/models/Stop_manager_model.php */
