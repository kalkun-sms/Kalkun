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
 * Way2sms_model Class
 *
 * Handle all messages database activity 
 * for Way2SMS <http://way2sms.com/>
 *
 * @package		Kalkun
 * @subpackage	Messages
 * @category	Models
 */
require_once('gammu_model'.EXT);

class Way2sms_model extends Gammu_model { 
	
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
	function send_messages($data)
	{
		$gateway = $this->config->item('gateway');
	    $this->sendWay2SMS($gateway['username'], $gateway['password'], $data['dest'], $data['message']);
    }

    /**
    * sendWay2SMS
    * Function to send to sms to single/multiple people via way2sms
    * @author Kingster
    * @category SMS
    * @example sendWay2SMS ( '9000012345' , 'password' , '987654321,9876501234' , 'Hello World')
    * @url https://github.com/kingster/Way2SMS-API/
    * @return String/Array
    * Please use this code on your own risk. The author is no way responsible for the outcome arising out of this
    * Good Luck!
    **/

    function sendWay2SMS($uid, $pwd, $phone, $msg)
    {

        $curl = curl_init();
        $timeout = 30;
        $result = array();

        $uid = urlencode($uid);
        $pwd = urlencode($pwd);

        $autobalancer = rand(1, 8);

        curl_setopt($curl, CURLOPT_URL, "http://site".$autobalancer.".way2sms.com/Login1.action");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "username=".$uid."&password=".$pwd."&button=Login");
        //curl_setopt($curl , CURLOPT_PROXY , '144.16.192.218:8080' );
        curl_setopt($curl, CURLOPT_COOKIESESSION, 1);
        curl_setopt($curl, CURLOPT_COOKIEFILE, "cookie_way2sms");
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 20);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.5) Gecko/2008120122 Firefox/3.0.5");
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_REFERER, "http://site".$autobalancer.".way2sms.com/");
        $text = curl_exec($curl);

        // Check if any error occured
        if (curl_errno($curl))
            return "access error : ". curl_error($curl);

        // Check for proper login
        $pos = stripos(curl_getinfo($curl, CURLINFO_EFFECTIVE_URL), "Main.action");
        if ($pos === "FALSE" || $pos == 0 || $pos == "")
            return "invalid login";

        if (trim($msg) == "" || strlen($msg) == 0)
            return "invalid message";
        
        $msg = urlencode(substr($msg, 0, 160));
        $pharr = explode(",", $phone);
        $refurl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
        curl_setopt($curl, CURLOPT_REFERER, $refurl);
        curl_setopt($curl, CURLOPT_URL, "http://site".$autobalancer.".way2sms.com/jsp/InstantSMS.jsp");
        $text = curl_exec($curl);

        preg_match_all('/<input[\s]*type="hidden"[\s]*name="Action"[\s]*id="Action"[\s]*value="?([^>]*)?"/si', $text, $match);
        $action = $match[1][0]; // get custid from the form fro the Action field in the post form

        foreach ($pharr as $p)
        {
            if(substr($p, 0, 3) == '+91')
                $p = substr($p, 3);

            if (strlen($p) != 10 || !is_numeric($p) || strpos($p, ".") != false)
            {
                $result[] = array('phone' => $p, 'msg' => urldecode($msg), 'result' => "invalid number");
                continue;
            }
            $p = urlencode($p);

            // Send SMS
            curl_setopt($curl, CURLOPT_URL, 'http://site'.$autobalancer.'.way2sms.com/quicksms.action');
            curl_setopt($curl, CURLOPT_REFERER, curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,
                "HiddenAction=instantsms&bulidgpwd=*******&bulidguid=username&catnamedis=Birthday&chkall=on&gpwd1=*******&guid1=username&ypwd1=*******&yuid1=username&Action=".
            $action."&MobNo=".$p."&textArea=".$msg);
            $contents = curl_exec($curl);

            //Check Message Status
    
            //preg_match_all('/<span class="style1">?([^>]*)?<\/span>/si', $contents, $match);
            //$out=str_replace("&nbsp;","",$match[1][0]);
            $pos = strpos($contents, 'Message has been submitted successfully');
            $res = ($pos !== false) ? true : false;
            $result[] = array('phone' => $p, 'msg' => urldecode($msg), 'result' => $res);
        }
        //echo $text;

        // Logout
        curl_setopt($curl, CURLOPT_URL, "http://site".$autobalancer.".way2sms.com/LogOut");
        curl_setopt($curl, CURLOPT_REFERER, $refurl);
        $text = curl_exec($curl);

        curl_close($curl);
        return $result;
    }

}

/* End of file way2sms_model.php */
/* Location: ./application/models/gateway/way2sms_model.php */
