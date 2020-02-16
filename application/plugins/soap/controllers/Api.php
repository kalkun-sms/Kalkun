<?php
/**
 *	@Author: bullshit "oskar@biglan.at"
 *	@Copyright: bullshit, 2010
 *	@License: GNU General Public License
*/
#include_once(APPPATH.'plugins/Plugin_Controller.php');
include_once(dirname(__FILE__).'/../libraries/nusoap.php');

class Api extends MY_Controller {
    public static $VERSION = "0.0.8";

    private static $NAMESPACE = "urn:KalkunRemoteAccess";
    private $ENDPOINT = "/plugin/soap/api";

    private $server;

    function __construct()
    {
        parent::__construct(FALSE);
        $this->load->model('Api_Model','api_model');
        $this->load->library('ApiSession','apisession');
        $this->load->model(array('Kalkun_model', 'Message_model'));
        log_message('info','init remote access api');
        
        $this->ENDPOINT = site_url($this->ENDPOINT);

        // FIXME - WORKAROUND FOR NUSOAPLIB
        $_SERVER['PHP_SELF'] = $this->ENDPOINT;

        $this->_initialze_soap_server();
    }

    function index()
    {
        log_message('debug','index');
        
        function version()
        {
            $CI =& get_instance();
            return $CI->getApiVersion();
        }
                
        function login($token)
        {
            $CI =& get_instance();
            $account = $CI->api_model->getAccount($token);

            if ($account['status'] == false)
                return 0;
                
            if ($account['ip'] == $_SERVER['REMOTE_ADDR'])
            {
                $CI->apisession->set_userdata('loggedin', 'TRUE');
                $CI->apisession->set_userdata('access_id',$account['id']);
                return $CI->apisession->userdata('session_id');
            }
            else
                $CI->session->set_userdata('loggedin', null);

            return 0;
        }
        
        function sendMessage($destinationNumber = '', $message = '')
        {
            $CI =& get_instance();
            $message = trim($message);
            $destinationNumber	= preg_replace("/^\+/","00",$destinationNumber);

            if(preg_match("/^\d+$/",$destinationNumber))
            {
                $CI->sendMessage($destinationNumber, $message, 1);
                return 1;
            }
            
            return 0;
        }
        
        function sendFlashMessage($destinationNumber = '', $message = '')
        {
            $CI =& get_instance();
            $message = trim($message);

            $destinationNumber	= preg_replace("/^\+/","00",$destinationNumber);

            if(preg_match("/^\d+$/",$destinationNumber))
            {
                $CI->sendMessage($destinationNumber, $message, 0);
                return 1;
            }

            return 0;
        }
        
        function logout()
        {
            $CI =& get_instance();
            $CI->apisession->sess_destroy();
            return 1;
        }

        $this->server->service(file_get_contents("php://input"));
    }

    function wsdl()
    {
        log_message('debug','wsdl');
        $_SERVER['QUERY_STRING'] = "wsdl";
        $this->server->service(file_get_contents("php://input"));
    }

    private function remoteAccessEnabled()
    {
        return true;
    }

    function _initialze_soap_server()
    {
        log_message('debug','init');
        $this->server = new soap_server();
        $this->server->configureWSDL('KalkunRemoteAccess', Api::$NAMESPACE,$this->ENDPOINT); 
                                                   
        $this->server->register('version',
            array(),                            // input parameters
            array('result' => 'xsd:string'),    // output parameter
            'urn:Api',                          // namespace
            $this->ENDPOINT."/getApiVersion",   // soapaction
            'rpc',                              // style
            'encoded',                          // use
            'API Version'                       // documentation
        );
        
        if ($this->remoteAccessEnabled())
        {
            $this->server->register('login',
                array('token' => 'xsd:string'),     // input parameters
                array('result' => 'xsd:string'),    // output parameter
                'urn:Api',                          // namespace
                $this->ENDPOINT."/login",           // soapaction
                'rpc',                              // style
                'encoded',                          // use
                'User login'                        // documentation
            );
            
            $this->server->register('sendMessage',
                array('destinationNumber' => 'xsd:string',      // input parameters
                    'message' => 'xsd:string'),
                array('result'	=> 'xsd:integer'),              // output parameter
                'urn:Api',                                      // namespace
                $this->ENDPOINT.'/sendMessage',                 // soapaction
                'rpc',                                          // style
                'encoded',                                      // use
                'Send SMS Message'                              // documentation
            );
            
            $this->server->register('sendFlashMessage',
                array('destinationNumber' => 'xsd:string',      // input parameters
                    'message' => 'xsd:string'),
                array('result'	=> 'xsd:integer'),              // output parameter
                'urn:Api',                                      // namespace
                $this->ENDPOINT.'/sendFlashMessage',            // soapaction
                'rpc',                                          // style
                'encoded',                                      // use
                'Send Flash SMS Message'                        // documentation
            );
            
            $this->server->register('logout',
                array(),                                        // input parameters
                array('result' => 'xsd:integer'),               // output parameter
                'urn:Api',                                      // namespace
                $this->ENDPOINT."/logout",                      // soapaction
                'rpc',                                          // style
                'encoded',                                      // use
                'User logout'                                   // documentation
            );
        }
    }

    public function sendMessage($dest = '', $message = '', $class = 1)
    {
        //TODO - NOTIFICATIONS
        
        $this->send($dest, $message, $class);
    }

    private function send($dest = '', $message = '', $class = 1)
    {
        $data['dest'] = $dest;
        $data['date'] = date('Y-m-d H:i:s');
        $data['message'] = $message;
        $data['coding'] = 'default';
        $data['uid'] = 1;
        $data['class'] = $class;
        $data['delivery_report'] = 'default';
        return $this->Message_model->send_messages($data);
    }

    public function getApiVersion()
    {
        return Api::$VERSION;
    }
}
?>
