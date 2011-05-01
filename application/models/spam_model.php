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
   
     function _check_spam($text)
     {
         $level = $this->b8->classify($text);
         $ret['class'] = ($level > $this->ratingcutoff) ? 'spam' : 'ham' ;
         $ret ['level'] = $level;
         return (object)$ret;
     }
     
     function apply_spam_filter($ID , $Text)
     {
        $is_spam = $this->_check_spam($Text);
        var_dump($is_spam);
        if($is_spam->class == 'spam')
        {
            if($is_spam->level > $this->ratingcutoff)
                $this->report_spam(array( 'ID' => $ID , 'Text' => $Text));
            return true;
        }
        return false;
     }
     
     function report_spam($params)
     {
        $this->b8->unlearn($params['Text'], b8::HAM);
        $this->b8->learn($params['Text'], b8::SPAM);
           
        //move message to spam folder
        $this->db->where('ID',$params['ID']);
        $this->db->update('inbox', array( 'id_folder' => '6' ));
        
     }
     
     function report_ham($params)
     {
        $this->b8->unlearn($params['Text'], b8::SPAM);
        $this->b8->learn($params['Text'], b8::HAM);
         
        //move message to spam folder
        $this->db->where('ID',$params['ID']);
        $this->db->update('inbox', array( 'id_folder' => '1' ));
     }
 
}