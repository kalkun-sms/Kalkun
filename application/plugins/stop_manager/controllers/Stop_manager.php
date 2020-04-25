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
 * Stop manager Class
 *
 * @package     Kalkun
 * @subpackage  Plugin
 * @category    Controllers
 */
include_once(APPPATH.'plugins/Plugin_Controller.php');

class Stop_manager extends Plugin_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('Kalkun_model');
        $this->load->model('Stop_manager_model');
    }

    function index()
    {
        if($_POST)
        {
            $this->Stop_manager_model->add(
                $this->input->post('destination_number',TRUE),
                $this->input->post('stop_type',TRUE),
                $this->input->post('stop_message',TRUE));
            redirect('plugin/stop_manager');
        }

        $this->load->library('pagination');
        $config['base_url'] = site_url('plugin/stop_manager');
        $config['total_rows'] = $this->Stop_manager_model->get('count');
        $config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
        $config['cur_tag_open'] = '<span id="current">';
        $config['cur_tag_close'] = '</span>';
        ($this->uri->segment(3,0) == "index") ? $config['uri_segment'] = 4 : $config['uri_segment'] = 3;
        $this->pagination->initialize($config);

        $offset = ($this->uri->segment(3,0) == "index") ? $this->uri->segment(4,0) : $this->uri->segment(3,0);
        if (!is_numeric($offset))
            show_404();
        if (intval($offset) >= $this->Stop_manager_model->get('count'))
            $offset = 0;

        $data['main'] = 'index';
        $data['stoplist'] = $this->Stop_manager_model->get('paginate', $config['per_page'], $offset);
        $data['number'] = $offset+1;
        $this->load->view('main/layout', $data);
    }

    function _remap($method, $params = array())
    {
        if (method_exists($this, $method))
        {
            return call_user_func_array(array($this, $method), $params);
        }
        else
        {
            if (is_numeric($method))
            {
                if (intval($method) < $this->Stop_manager_model->get('count'))
                {
                    $this->index($method);
                    return;
                }
                else
                {
                    $this->index();
                    return;
                }
            }
            show_404();
        }
    }

    function delete($from, $type)
    {
        $this->Stop_manager_model->delete($from, $type);
        redirect('plugin/stop_manager');
    }

#    _____ ___ ___ _____   __  __ ___ _____ _  _  ___  ___  ___
#   |_   _| __/ __|_   _| |  \/  | __|_   _| || |/ _ \|   \/ __|
#     | | | _|\__ \ | |   | |\/| | _|  | | | __ | (_) | |) \__ \
#     |_| |___|___/ |_|   |_|  |_|___| |_| |_||_|\___/|___/|___/
#

    function test_stop_manager_incoming() {
        // To test, you need to comment out the lines starting with "add_action"
        // at the top of ./application/plugins/stop_manager/stop_manager.php
        // and comment out the show_404() statement here

        show_404();
        include ('application/plugins/stop_manager/stop_manager.php');

        $msg = array(
            "test ACTIVER raPpel",
            "test StoP raPpel",
            "test ACTIVER annul",
            "test CANCel annul2",
            "test yes annul",
            "test ACTIVER annul2",
            "STOP",
            "invalide",
        );

        for ($i=0; $i<sizeof($msg); $i++)
        {
            $sms = new stdClass();
            $sms->TextDecoded = $msg[$i];
            $sms->SenderNumber = '+336' . sprintf('%08d', 5000 + $i);

            stop_manager_incoming($sms);
        }
    }

    function test_stop_manager_cleanup_outgoing() {
        // To test, you need to comment out the lines starting with "add_action"
        // at the top of ./application/plugins/stop_manager/stop_manager.php
        // and comment out the show_404() statement here

        show_404();
        include ('application/plugins/stop_manager/stop_manager.php');

        $data['message'] = "aaa ~rappel~";    // Type existant
        //$data['message'] = "aaa ~z~";         // Type non existant
        //$data['message'] = "aaa ~~";          // Type vide
        //$data['message'] = "aaa";             // Type non renseigné

        $nums = array();
        for ($i=0; $i<10; $i++)
        {
            $nums[$i] = '+336' . sprintf('%08d', 5000 + $i);
        }
        $dest = array_merge(array('0612345678', '0611111111'), $nums);

        stop_manager_cleanup_outgoing(array($dest, $data));
    }
}

/* End of file Stop_manager.php */
/* Location: ./application/plugins/stop_manager/models/Stop_manager.php */
