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
 * Spam_Check Class
 *
 * Handle all user database activity 
 *
 * @package		Kalkun
 * @subpackage	Spam_model
 * @category	Models
 */
class Spam_model extends Model {

    var $classifier;
	/**
	 * Constructor
	 *
	 * @access	public
	 */		
	function Spam_model()
	{
		parent::Model();
        $this->load->library('classifier-php/bayes.php');
        $this->classifier = new Bayes('spam','ham');
	}

	// --------------------------------------------------------------------
	
	 function _train_model()
     {
        $_data = $this->db->get('spam_check');
        foreach ($_data->result() as $row)
        {
            var_dump($row);
            //$this->classifier->train($row->category, $row->token); 
            $this->classifier->train('spam', $row->token);
        }
        // spam from user data
        $this->db->select('TextDecoded');
        $this->db->distinct();
        $this->db->where('id_folder', '6');
        $_data = $this->db->get('inbox');
        foreach ($_data->result() as $row)
        {
            var_dump($row);
            $this->classifier->train('spam', $row->TextDecoded); 
        }
        
        
        //now train good data from user inbox and outbox
        
        $this->db->distinct();
        $this->db->select('TextDecoded');
        $this->db->where("`id_folder` != '6' ");
        $this->db->where('Processed', 'false');
        $_data = $this->db->get('inbox');
        $this->db->limit(100);
        foreach ($_data->result() as $row)
        {//var_dump($row);
            $this->classifier->train('ham', $row->TextDecoded); 
        }
        
        $this->db->distinct();
        $this->db->select('TextDecoded');
        $this->db->where("`id_folder` != '6' ");
        $_data = $this->db->get('sentitems');
        $this->db->limit(100);
        foreach ($_data->result() as $row)
        {//var_dump($row);
            $this->classifier->train('ham', $row->TextDecoded); 
        }
        
        
     }
     
     
     function _check_spam($text)
     {
         $this->_train_model();
         return $this->classifier->classify($text);
     } 
     
     function apply_spam_filter($ID , $Text)
     {
        $is_spam = $this->_check_spam($Text);
        var_dump($this->classifier->classifications($Text));
        var_dump($is_spam);
        if($is_spam == 'spam')
        {
            //$this->report_spam(array( 'ID' => $ID , 'Text' => $Text));
            return true;
        }
        return false;
     }
     
     function report_spam($params)
     {
        // id provided
        $data = array( 'id_inbox' =>  $params['ID'],
           'token' => $params['Text'] ,
           'rating' => '1',
           'category' => 'spam' );
        $this->db->insert('spam_check', $data);    
         
        //move message to spam folder
        $this->db->where('ID',$params['ID']);
        $this->db->update('inbox', array( 'id_folder' => '6' ));
        
     }
     
     function report_ham($params)
     {
        // id provided
        $this->db->delete('spam_check', array('id_inbox' => $params['ID'])); 
 
        //move message to spam folder
        $this->db->where('ID',$params['ID']);
        $this->db->update('inbox', array( 'id_folder' => '1' ));
        
     }

}	

 