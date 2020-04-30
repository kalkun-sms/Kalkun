<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		http://kalkun.sourceforge.net/license.php
 * @link		http://kalkun.sourceforge.net
 */

// ------------------------------------------------------------------------

/**
 * Connekt_model Class
 *
 * Handle all messages database activity 
 * for Connekt <https://github.com/kingster/connekt>
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Models
 */
require_once('nongammu_model'.EXT);

class Connekt_model extends nongammu_model { 
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();
	}
	
    // --------------------------------------------------------------------
	
	/**
	 * Send Messages (Still POC)
	 * 
	 * @return void
	 */	
	function really_send_messages($data)
	{
        $gateway = $this->config->item('gateway');
        $p = $data['dest'];

        $payload = array (
            'channelData' => array (
                'type' => 'SMS',
                'body' => $data['message'],
            ),
            'channelInfo' => array (
                'receivers' => array($p),
                'type' => 'SMS',
            ),
            'sla' => 'H',
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $gateway["url"],
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($payload),
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "x-api-key: ".$gateway["api_id"]
          ),
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $is_succes = ($httpcode >= 200 && $httpcode < 300 ) ? true : false;
        if ($is_succes)
            return  $result[] = array('phone' => $p, 'msg' => $response, 'result' => $is_succes);
        else
            return $$response;
    }

   


}

/* End of file Connekt_model.php */
/* Location: ./application/models/gateway/Connekt_model.php */
