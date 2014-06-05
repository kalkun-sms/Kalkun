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
 * Tmobilecz_model Class
 *
 * Handle the real sending of SMS
 * for T-Mobile CZ <https://sms.t-mobile.cz/>
 * Database handling inherited from nongammu_model
 *
 * @package	Kalkun
 * @subpackage	Messages
 * @category	Models
 */
require_once('nongammu_model'.EXT);

class Tmobilecz_model extends nongammu_model { 
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();
		log_message('debug',"TMCZ> Direct Gateway TMobileCZ Class Initialized");
	}
	
    // --------------------------------------------------------------------
	
	/**
	 * Real Send Messages
	 * 
	 * @access	public   		 
	 * @param	mixed $options 
	 * Option: Values
	 * --------------
	 * dest string, phone number destination
	 * date datetime
	 * message string
	 * coding default, unicode
	 * class -1, 0, 1
	 * delivery_report default, yes, no
	 * uid int
	 * @return null/string
	 */	
	function really_send_messages($data)
	{
            $gateway = $this->config->item('gateway');
	    if(!is_array($gateway['tmobileczauth'])) {
	        log_message('error',"TMCZ> Authentication not configured in kalkun_settings.php. SMS aborted.");
		return "Authentication not configured in kalkun_settings.php.";
	    };
	    $auth=$gateway['tmobileczauth'];
	    if (($user=$auth[$data['uid']]['user'])&&($pass=$auth[$data['uid']]['pass'])){
	        log_message('debug',"TMCZ> Found credentials for user ID ".$data['uid']);
		$hist=($auth[$data['uid']]['hist']==true);
		$eml=$auth[$data['uid']]['eml'];
	    }elseif(($user=$auth['default']['user'])&&($pass=$auth['default']['pass'])){
	        log_message('debug',"TMCZ> Found default credentials for all users.");
		$hist=($auth['default']['hist']==true);
                $eml=$auth['default']['eml'];
	    }else{
	        log_message('error',"TMCZ> Aborting SMS. No credentials to send SMS via ".
                           __CLASS__." to ".$data['dest']." for user ID ".$data['uid']);
		return "No credentials to send SMS.";
	    };
	    log_message('debug',"TMCZ> SMS via ".__CLASS__." user ".$user." to ".$data['dest'].
	                        " length ".strlen($data['message'])." chars");
	    $ret=$this->sendTMobileCZ($user, $pass, $data['dest'], $data['message'],
                                      $data['class']=="0",$data['delivery_report']=="yes",$hist,$eml);
	    if(is_string($ret)){
	        log_message('error',"TMCZ> SMS via ".__CLASS__." to ".$data['dest']." failed: ".$ret);
		return $ret;
	    };
        }

    /**
    * sendTMobileCZ
    * Function to send to sms to single/multiple people via T-Mobile CZ
    * @author jbubik
    * @category SMS
    * @example sendTMobileCZ ( 'user' , 'password' , '736123456,605123456' , 'Hello World')
    * @url https://github.com/jbubik/Kalkun
    * @return String/Array
    * Please use this code on your own risk. The author is no way responsible for the outcome arising out of this
    * Good Luck!
    **/

    function sendTMobileCZ($uid, $pwd, $phone, $msg, $isFlash=false, $dRpt=false, $hist=false, $emlCopy='')
    {

        if (($curl=curl_init())===false)
	    return "TMCZ> CURL init failed!";
	$cv=curl_version();
	log_message('debug',"TMCZ> CURL version: ".$cv["version"].", SSL version: ".$cv["ssl_version"].
	                    ", LIBZ version: ".$cv["libz_version"].", protocols: ".implode($cv["protocols"],"+"));
        $timeout = 30;
        $result = array();

        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, false); //false=>use previous session cookies
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_VERBOSE, false);  //set TRUE to see CURL transfers in error.log
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 20);
	$cache_path = (($path=$this->config->item('cache_path')) == '') ? BASEPATH.'cache/' : $path;
	$cookies=$cache_path."cookie_".__CLASS__."_".$uid;
	if (! is_really_writable($cache_path))
	    return "Cookie file $cookies not writable";
        if ((($cookiemt=filemtime($cookies))!==false)&&((time()-$cookiemt)>300)) //cookies older than 5mins
            unlink($cookies);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookies);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookies);
        curl_setopt($curl, CURLOPT_URL, "https://sms.t-mobile.cz/closed.jsp");
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:29.0) Gecko/20100101 Firefox/29.0");
        curl_setopt($curl, CURLOPT_REFERER, "");
        log_message('debug',"TMCZ> getting first page...");
        $text = curl_exec($curl);

        // Check if any error occured
        if (curl_errno($curl))
            return "CURL error : ". curl_error($curl);
	log_message('info',"TMCZ> GET https://sms.t-mobile.cz/closed.jsp RESULT:\n".$text."\n---EOF---");

        // search if we are already logged in
        if (strpos($text, "/.gang/logout")===false) {
            curl_setopt($curl, CURLOPT_REFERER, curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) );
            curl_setopt($curl, CURLOPT_URL, "https://www.t-mobile.cz/.gang/login/tzones");
	    curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS,
                "nextURL=checkStatus.jsp&errURL=clickError.jsp&".
                "username=".urlencode($uid)."&remember=1&".
		"password=".urlencode($pwd)."&submit=Přihlásit");
            log_message('debug',"TMCZ> logging in...");
            $text = curl_exec($curl);
             
            // Check if any error occured
            if (curl_errno($curl))
                return "CURL error : ". curl_error($curl);
	    log_message('info',"TMCZ> POST https://www.t-mobile.cz/.gang/login/tzones RESULT:\n".$text."\n---EOF---");

            if (strpos($text, "/.gang/logout")===false){
                if(preg_match('|<p\sclass="text-orange\stext-size-2">(.+)\n|u',$text,$matches))
                    return "Invalid login. Error: ".$matches[1];
                return "Invalid login. Unknown error.";
	    };
        };

        if(!preg_match('|<input\stype="hidden"\sname="counter"\svalue="([0-9a-zA-Z]+)"\s/>|',$text,$matches))
            return "Security code not found";
        log_message('debug',"TMCZ> Security code: ".$matches[1]);
        
        curl_setopt($curl, CURLOPT_REFERER, curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) );
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_URL, "https://sms.t-mobile.cz/closed.jsp");
        curl_setopt($curl, CURLOPT_POSTFIELDS,
                "counter=".$matches[1]."&".
		"recipients=".urlencode($phone)."&".
		"text=".urlencode($msg)."&".
		"mtype=".($isFlash?'1':'0')."&". //0-regular SMS, 1-flash SMS
		//"TMCZcheck=on&",
		($dRpt?"confirmation=1&":"").  //confirm SMS delivery
		($hist?"history=on&":"").      //save in provider's history
		"email=".urlencode($emlCopy)); //provider will send a copy to e-mail
        log_message('debug',"TMCZ> sending SMS...");
        $text = curl_exec($curl);

        // Check if any error occured
        if (curl_errno($curl))
            return "CURL error : ". curl_error($curl);
	log_message('info',"TMCZ> POST https://sms.t-mobile.cz/closed.jsp RESULT:\n".$text."\n---EOF---");

        // Check for proper SMS sending
        if (!preg_match('|SMS zpr.v. byl. odeslán.|u',$text)&&!preg_match('|SMS was sent|u',$text)
          &&!preg_match('|All SMS messages were sent|u',$text)) {
            if(preg_match('|<p class="text-red text-size-2">(.+)</p>|u',$text,$matches))
              return "Error sending SMS: ".$matches[1];
	    else
	      return "Error sending SMS: unknown error.";
	};
	log_message('debug',"TMCZ> SMS sent successfully.");

        curl_close($curl);
        //$result[] = array('phone' => $p, 'msg' => urldecode($msg), 'result' => $res);
        return $result;
    }

}

/* End of file tmobilecz_model.php */
/* Location: ./application/models/gateway/tmobilecz_model.php */
