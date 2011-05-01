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
require(dirname(__FILE__) .'/../libraries/b8/b8.php');
        
 
/**
 * Spam_Check Class
 *
 * Handle all user database activity 
 *
 * @package		Kalkun
 * @subpackage	Spam_model
 * @category	Models
 */
class Spam_model extends Model {
 
    public $classifier;
    public $ratingcutoff = 0.7;
    public $b8;
 
    /**
     * Constructor
     *
     * @access	public
     */		
    function Spam_model()
    {
    	parent::Model();
        $b8_config = array( 'storage' => 'active');
        $config_database= array(); // not required for activare record, see b8 documentation for mysql/dba methods
        $this->b8 = new b8($b8_config, $config_database );
        $started_up = $this->b8->validate();
        if($started_up !== TRUE) 		
            die( "<b> Could not initialize b8. error code: $started_up</b>");
    }
 
    // --------------------------------------------------------------------
   
     /**
      * Spam_model::_check_spam()
      * Check if a a text is spam
      * @param text $text
      * @return
      */
     function _check_spam($text)
     {
         $level = $this->b8->classify($text);
         $ret['class'] = ($level > $this->ratingcutoff) ? 'spam' : 'ham' ;
         $ret ['level'] = $level;
         return (object)$ret;
     }
     
     /**
      * Spam_model::apply_spam_filter()
      * 
      * @param mixed $ID
      * @param mixed $Text
      * @return
      */
     function apply_spam_filter($ID , $Text)
     {
        $is_spam = $this->_check_spam($Text);
        if($is_spam->class == 'spam')
        {
            if($is_spam->level > $this->ratingcutoff)
                $this->report_spam(array( 'ID' => $ID , 'Text' => $Text));
            //move to spam folder    
            $this->db->where('ID',$ID)->update('inbox', array( 'id_folder' => '6' ));
        
            return true;
        }
        return false;
     }
     
     /**
      * Spam_model::report_spam()
      * 
      * @param mixed $params
      * @return
      */
     function report_spam($params)
     {
        $this->b8->learn($params['Text'], b8::SPAM);
           
        //move message to spam folder
        $this->db->where('ID',$params['ID']);
        $this->db->update('inbox', array( 'id_folder' => '6' ));
        
        $this->_cloud_report('spam',$params['Text']);
        
     }
     
     /**
      * Spam_model::report_ham()
      * 
      * @param mixed $params
      * @return
      */
     function report_ham($params)
     {
        $this->b8->learn($params['Text'], b8::HAM);
         
        //move message to spam folder
        $this->db->where('ID',$params['ID']);
        $this->db->update('inbox', array( 'id_folder' => '1' ));
        
        $this->_cloud_report('ham',$params['Text']);
     }
     
     /**
      * Spam_model::_cloud_report()
      * 
      * @param mixed $type
      * @param mixed $text
      * @return
      */
     function _cloud_report($type, $text)
     {
        $this->load->library('curl'); 
        $this->curl->create('http://digitalplantation.org/kalkun/cloudspam/report.php');
        $post = array('type'=>$type , 'msg' => $text);
        $this->curl->post($post);
        
        if($this->config->item('enable_proxy'))
        {
            $this->curl->proxy($this->config->item('proxy_host'), $this->config->item('proxy_port'));
            if($this->config->item('proxy_username') != '')
                $this->curl->proxy_login($this->config->item('proxy_username'),$this->config->item('proxy_password'));
        }
        
        echo $this->curl->execute();

     }
 
}