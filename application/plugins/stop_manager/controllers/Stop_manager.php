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
        if($_POST && is_null($this->input->post('search_name')))
        {
            $this->Stop_manager_model->add(
                $this->input->post('destination_number',TRUE),
                $this->input->post('stop_type',TRUE),
                $this->input->post('stop_message',TRUE));
            redirect('plugin/stop_manager');
        }

        $offset = 0;
        if (!is_null($this->input->post('search_name')))
        {
            $data['stoplist'] = $this->Stop_manager_model->get('search');
        }
        else
        {
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
            $data['stoplist'] = $this->Stop_manager_model->get('paginate', $config['per_page'], $offset);
        }

        $data['main'] = 'index';
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
        $this->Stop_manager_model->delete(base64_decode(urldecode($from)), base64_decode(urldecode($type)));
        redirect('plugin/stop_manager');
    }
}

/* End of file Stop_manager.php */
/* Location: ./application/plugins/stop_manager/models/Stop_manager.php */
