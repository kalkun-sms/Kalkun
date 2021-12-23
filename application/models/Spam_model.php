<?php
/**
 * Kalkun
 * An open source web based SMS Management
 *
 * @package		Kalkun
 * @author		Kalkun Dev Team
 * @license		https://spdx.org/licenses/GPL-3.0-or-later.html
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
class Spam_model extends CI_Model {

	public $classifier;
	public $ratingcutoff = 0.7;
	public $b8;

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		parent::__construct();

		// get database engine
		$this->load->helper('kalkun');
		$db_engine = $this->db->platform();
		$db_driver = get_database_property($db_engine)['name'];

		switch ($db_driver)
		{
			case 'mysqli':
				// value of storage should be then name of the backend file in libraries/b8/storage
				$config_b8 = ['storage' => 'mysql'];
				break;
			case 'postgre':
				$config_b8 = ['storage' => 'pgsql'];
				break;
			case 'sqlite':
				$config_b8 = ['storage' => 'sqlite'];
				break;
			default:
		}

		$config_storage = [
			'resource' => $this->db->conn_id,
			'table' => 'b8_wordlist'];

		// We use the default lexer settings
		$config_lexer = [];

		// We use the default degenerator configuration
		$config_degenerator = [];

		try
		{
			$this->b8 = new b8\b8($config_b8, $config_storage, $config_lexer, $config_degenerator);
		}
		catch (Exception $e)
		{
			log_message('error', "Could not initialize b8 library. {$e->getMessage}()");
			show_message("Could not initialize b8 library. {$e->getMessage}()", 500, '500 Internal Server Error');
		}
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
		$ret['level'] = $level;
		return (object)$ret;
	}

	/**
	 * Spam_model::apply_spam_filter()
	 *
	 * @param mixed $ID
	 * @param mixed $Text
	 * @return
	 */
	function apply_spam_filter($ID, $Text)
	{
		$is_spam = $this->_check_spam($Text);
		if ($is_spam->class === 'spam')
		{
			if ($is_spam->level > $this->ratingcutoff)
			{
				$this->report_spam(array('ID' => $ID, 'Text' => $Text));
			}
			//move to spam folder
			$this->db->where('ID', $ID)->update('inbox', array('id_folder' => '6'));

			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Spam_model::report_spam()
	 *
	 * @param mixed $params
	 * @return
	 */
	function report_spam($params)
	{
		$this->b8->learn($params['Text'], b8\b8::SPAM);

		//move message to spam folder
		$this->db->where('ID', $params['ID']);
		$this->db->update('inbox', array('id_folder' => '6'));
	}

	/**
	 * Spam_model::report_ham()
	 *
	 * @param mixed $params
	 * @return
	 */
	function report_ham($params)
	{
		$this->b8->learn($params['Text'], b8\b8::HAM);

		//move message to spam folder
		$this->db->where('ID', $params['ID']);
		$this->db->update('inbox', array('id_folder' => '1'));
	}
}
