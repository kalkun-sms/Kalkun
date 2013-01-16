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
 * SMS_credit Class
 *
 * @package     Kalkun
 * @subpackage  Plugin
 * @category    Controllers
 */
include_once(APPPATH.'plugins/Plugin_Controller.php');

class SMS_credit extends Plugin_Controller {

    /**
     * Constructor
     *
     * @access	public
     */
    function __construct()
    {
        parent::Plugin_Controller();
        $this->load->model('sms_credit_model', 'plugin_model');
    }

    // --------------------------------------------------------------------

    /**
     * Index
     *
     * Display list of all users
     *
     * @access  public
     */		
    function index()
    {
        $param = array();
        if($_POST)
        {
            $param['q'] = $this->input->post('search_name');
            $data['query'] = $param['q'];
        }

        $this->load->library('pagination');
        $config['base_url'] = site_url('plugin/sms_credit/index');
        $config['total_rows'] = $this->plugin_model->get_users()->num_rows();
        $config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
        $config['cur_tag_open'] = '<span id="current">';
        $config['cur_tag_close'] = '</span>';
        $config['uri_segment'] = 4;
        $this->pagination->initialize($config);
        $param['limit'] = $config['per_page'];
        $param['offset'] = $this->uri->segment(4,0);

        $data['main'] = 'index';
        $data['title'] = 'Users Credit';
        $data['users'] = $this->plugin_model->get_users($param);
        $data['packages'] = $this->plugin_model->get_packages();
        
        $this->load->view('main/layout', $data);
    }

    // --------------------------------------------------------------------

    /**
     * Add Users
     *
     * Add an User with packages
     *
     * @access  public
     */		
    function add_users()
    {
        if($_POST)
        {
            $param['id_user'] = $this->input->post('id_user');
            $param['id_template_credit'] = $this->input->post('package');
            $param['valid_start'] = $this->input->post('package_start');
            $param['valid_end'] = $this->input->post('package_end');

            if(empty($param['id_user']))
            {
                unset($param['id_user']);
                $param['realname'] = trim($this->input->post('realname'));
                $param['username'] = trim($this->input->post('username'));
                $param['phone_number'] = $this->input->post('phone_number');
                $param['level'] = $this->input->post('level');
                $param['password'] = sha1($this->input->post('password'));
                $this->plugin_model->add_users($param);
            }
            else
            {
                 $this->plugin_model->change_users_package($param);
            }
           
            redirect('plugin/sms_credit');
        }
    }

    // --------------------------------------------------------------------

    /**
     * Delete Users
     *
     * Delete an User
     *
     * @access  public
     */		
    function delete_users($id = NULL)
    {
        $this->plugin_model->delete_users($id);
        redirect('plugin/sms_credit');
    }

    // --------------------------------------------------------------------

    /**
     * Packages
     *
     * Display list of all packages
     *
     * @access  public
     */		
    function packages()
    {
        $this->load->library('pagination');
        $config['base_url'] = site_url('plugin/sms_credit/packages');
        $config['total_rows'] = $this->plugin_model->get_packages()->num_rows();
        $config['per_page'] = $this->Kalkun_model->get_setting()->row('paging');
        $config['cur_tag_open'] = '<span id="current">';
        $config['cur_tag_close'] = '</span>';
        $config['uri_segment'] = 4;
        $this->pagination->initialize($config);
        $param['limit'] = $config['per_page'];
        $param['offset'] = $this->uri->segment(4,0);

        if($_POST)
        {
            $data['query'] = $this->input->post('query');
            $data['packages'] = $this->plugin_model->search_packages($data['query']);
        }
        else
        {
            $data['packages'] = $this->plugin_model->get_packages($param);
        }

        $data['main'] = 'packages';
        $data['title'] = 'Credit Package';
        $this->load->view('main/layout', $data);
    }

    // --------------------------------------------------------------------

    /**
     * Add Packages
     *
     * Add Packages
     *
     * @access  public
     */		
    function add_packages()
    {
        if($_POST)
        {
            $param['id_credit_template'] = $this->input->post('id_package');

            if(empty($param['id_credit_template'])) {
                unset($param['id_credit_template']);
            }

            $param['template_name'] = trim($this->input->post('package_name'));
            $param['sms_numbers'] = trim($this->input->post('sms_amount'));
            $this->plugin_model->add_packages($param);
            redirect('plugin/sms_credit/packages');
        }
    }

    // --------------------------------------------------------------------

    /**
     * Delete Packages
     *
     * Delete Packages
     *
     * @access  public
     */		
    function delete_packages($id = NULL)
    {
        $this->plugin_model->delete_packages($id);
        redirect('plugin/sms_credit/packages');
    }

}

/* End of file sms_credit.php */
/* Location: ./application/plugins/sms_credit/controllers/sms_credit.php */
