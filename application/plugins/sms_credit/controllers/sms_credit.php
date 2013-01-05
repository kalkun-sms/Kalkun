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

    /**
     * Packages
     *
     * Display list of all packages
     *
     * @access  public
     */		
    function packages()
    {
        $data['main'] = 'packages';
        $data['title'] = 'Credit Package';
        $data['packages'] = $this->plugin_model->get_packages();
        
        $this->load->view('main/layout', $data);
    }


}

/* End of file sms_credit.php */
/* Location: ./application/plugins/sms_credit/controllers/sms_credit.php */
