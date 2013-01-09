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
        $data['main'] = 'index';
        $data['title'] = 'Users Credit';
        $data['users'] = $this->plugin_model->get_users();
        
        $this->load->view('main/layout', $data);
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
        if($_POST)
        {
            $data['query'] = $this->input->post('query');
            $data['packages'] = $this->plugin_model->search_packages($data['query']);
        }
        else
        {
            $data['packages'] = $this->plugin_model->get_packages();
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

}

/* End of file sms_credit.php */
/* Location: ./application/plugins/sms_credit/controllers/sms_credit.php */
