<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

// SQLite3 PDO driver v.0.02 by Xintrea
// Tested on CodeIgniter 1.7.1
// Based on CI_DB_pdo_driver class v.0.1
// Warning! This PDO driver work with SQLite3 only!

/**
 * Code Igniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		Rick Ellis
 * @copyright	Copyright (c) 2006, pMachine, Inc.
 * @license		http://www.codeignitor.com/user_guide/license.html
 * @link		http://www.codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------



/**
 * PDO Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the active record
 * class is being used or not.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		Dready
 * @link		http://dready.jexiste.fr/dotclear/
 */

class CI_DB_pdo_driver extends CI_DB {

// Added by Xi
        var $dbdriver = 'pdo';
        var $_escape_char = ''; // The character used to escape with - not needed for SQLite
        var $conn_id;
        var $_random_keyword = ' Random()'; // database specific random keyword

	/**
	 * Non-persistent database connection
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */
	function db_connect()
	{
		$conn_id = false;
		try {
			$conn_id = new PDO ($this->database, $this->username, $this->password);
			log_message('debug', "PDO driver connecting ".$this->database);
		} catch (PDOException $e) {
			log_message('debug','merde');
			log_message('error', $e->getMessage());
			if ($this->db_debug)
            		{
				$this->display_error($e->getMessage(), '', TRUE);
            		}
		}
		log_message('debug',print_r($conn_id,true));
		if ( $conn_id ) {
			log_message('debug','PDO driver connection ok');
		}

                // Added by Xi
                $this->conn_id=$conn_id;

		return $conn_id;
	}

	// --------------------------------------------------------------------

	/**
	 * Persistent database connection
	 *
	 * @access	private, called by the base class
	 * @return	resource
	 */
	function db_pconnect()
	{
	 // For SQLite architecture can not enable persistent connection
	 return $this->db_connect();
	
	/*
		$conn_id = false;
		try {
			$conn_id = new PDO ($this->database, $this->username, $this->password, array(PDO::ATTR_PERSISTENT => true) );
		} catch (PDOException $e) {
			log_message('error', $e->getMessage());
			if ($this->db_debug)
            		{
				$this->display_error($e->getMessage(), '', TRUE);
            		}
		}

                // Added by Xi
                $this->conn_id=$conn_id;

		return $conn_id;
	*/
	}

	// --------------------------------------------------------------------

	/**
	 * Select the database
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */
	function db_select()
	{
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Execute the query
	 *
	 * @access	private, called by the base class
	 * @param	string	an SQL query
	 * @return	resource
	 */
	function _execute($sql)
	{
		$sql = $this->_prep_query($sql);
		log_message('debug','SQL : '.$sql);
		return @$this->conn_id->query($sql);
	}

	// --------------------------------------------------------------------

	/**
	 * Prep the query
	 *
	 * If needed, each database adapter can prep the query string
	 *
	 * @access	private called by execute()
	 * @param	string	an SQL query
	 * @return	string
	 */
    function &_prep_query($sql)
    {
		return $sql;
    }


// Modify by Xi
	/**
	 * "Smart" Escape String
	 *
	 * Escapes data based on type
	 * Sets boolean and null types
	 *
	 * @access	public
	 * @param	string
	 * @return	integer
	 */
	function escape($str)
	{
		switch (gettype($str))
		{
			case 'string'	:	$str = "'".$this->escape_str($str)."'";
				break;
			case 'boolean'	:	$str = ($str === FALSE) ? 0 : 1;
				break;
			default			:	$str = ($str === NULL) ? 'NULL' : $str;
				break;
		}

		return $str;
	}


	// --------------------------------------------------------------------

	/**
	 * Escape String
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
/*         
	function escape_str($str)
	{
		if (get_magic_quotes_gpc())
		{
			$str = stripslashes($str);
		}
		return $this->conn_id->quote($str);
	}
*/
	// --------------------------------------------------------------------


// Added by Xi
/**         
* Escape String         
*         
* @access      public         
* @param       string         
* @return      string         
*/        
function escape_str($str)        
{
 return sqlite_escape_string($str);        
}


// Added by Xi
/** * Escape the SQL Identifiers * 
* This function escapes column and table names * 
* @accessprivate 
* @paramstring 
* @returnstring */
function _escape_identifiers($item)
{
 if ($this->_escape_char == '')
  {
   return $item;
  }

 foreach ($this->_reserved_identifiers as $id)
  {
   if (strpos($item, '.'.$id) !== FALSE)
    {
     $str = $this->_escape_char. str_replace('.', $this->_escape_char.'.', $item);  
                                
     // remove duplicates if the user already included the escape
     return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
    }               
  }
        
 if (strpos($item, '.') !== FALSE)
  {
   $str = $this->_escape_char.str_replace('.', $this->_escape_char.'.'.$this->_escape_char, $item).$this->_escape_char;                    
  }
 else
  {
   $str = $this->_escape_char.$item.$this->_escape_char;
  }
                
 // remove duplicates if the user already included the escape
 return preg_replace('/['.$this->_escape_char.']+/', $this->_escape_char, $str);
}


// Add by Xi
	/**
	 * Begin Transaction
	 *
	 * @access	public
	 * @return	bool		
	 */	
	function trans_begin($test_mode = FALSE)
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}
		
		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
			return TRUE;
		}

		// Reset the transaction failure flag.
		// If the $test_mode flag is set to TRUE transactions will be rolled back
		// even if the queries produce a successful result.
		$this->_trans_failure = ($test_mode === TRUE) ? TRUE : FALSE;

		$this->simple_query('BEGIN TRANSACTION');
		return TRUE;
	}
	// --------------------------------------------------------------------


// Add by Xi
	/**
	 * Commit Transaction
	 *
	 * @access	public
	 * @return	bool		
	 */	
	function trans_commit()
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
			return TRUE;
		}

		$this->simple_query('COMMIT');
		return TRUE;
	}
	// --------------------------------------------------------------------


// Add by Xi
	/**
	 * Rollback Transaction
	 *
	 * @access	public
	 * @return	bool		
	 */	
	function trans_rollback()
	{
		if ( ! $this->trans_enabled)
		{
			return TRUE;
		}

		// When transactions are nested we only begin/commit/rollback the outermost ones
		if ($this->_trans_depth > 0)
		{
			return TRUE;
		}

		$this->simple_query('ROLLBACK');
		return TRUE;
	}
	
	// --------------------------------------------------------------------


	/**
	 * Close DB Connection
	 *
	 * @access	public
	 * @param	resource
	 * @return	void
	 */
	function destroy($conn_id)
	{
		$conn_id = null;
	}



	// --------------------------------------------------------------------

	/**
	 * Insert ID
	 *
	 * @access	public
	 * @return	integer
	 */
	function insert_id()
	{
		return @$this->conn_id->lastInsertId();
	}

	// --------------------------------------------------------------------

	/**
	 * "Count All" query
	 *
	 * Generates a platform-specific query string that counts all records in
	 * the specified database
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function count_all($table = '')
	{
		if ($table == '')
			return '0';

		$query = $this->query("SELECT COUNT(*) AS numrows FROM `".$table."`");

		if ($query->num_rows() == 0)
			return '0';

		$row = $query->row();
		return $row->numrows;
	}

	// --------------------------------------------------------------------

	/**
	 * The error message string
	 *
	 * @access	private
	 * @return	string
	 */
	function _error_message()
	{
		$infos = $this->conn_id->errorInfo();
		return $infos[2];
	}

	// --------------------------------------------------------------------

	/**
	 * The error message number
	 *
	 * @access	private
	 * @return	integer
	 */
	function _error_number()
	{
		$infos = $this->conn_id->errorInfo();
		return $infos[1];
	}

	// --------------------------------------------------------------------

	/**
	 * Version number query string
	 *
	 * @access	public
	 * @return	string
	 */
	function version()
	{
		return $this->conn_id->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
	}

	// --------------------------------------------------------------------

	/**
	 * Escape Table Name
	 *
	 * This function adds backticks if the table name has a period
	 * in it. Some DBs will get cranky unless periods are escaped
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	string
	 */
	function escape_table($table)
	{
		if (stristr($table, '.'))
		{
			$table = preg_replace("/\./", "`.`", $table);
		}

		return $table;
	}

	// --------------------------------------------------------------------

	/**
	 * Field data query
	 *
	 * Generates a platform-specific query so that the column data can be retrieved
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	object
	 */
	function _field_data($table)
	{
		$sql = "SELECT * FROM ".$this->escape_table($table)." LIMIT 1";
		$query = $this->query($sql);
		return $query->field_data();
	}

	// --------------------------------------------------------------------

	/**
	 * Insert statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the insert keys
	 * @param	array	the insert values
	 * @return	string
	 */
	function _insert($table, $keys, $values)
	{
		return "INSERT INTO ".$this->escape_table($table)." (".implode(', ', $keys).") VALUES (".implode(', ', $values).")";
	}

	// --------------------------------------------------------------------

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the update data
	 * @param	array	the where clause
	 * @return	string
	 */
	function _update($table, $values, $where)
	{
		foreach($values as $key => $val)
		{
			$valstr[] = $key." = ".$val;
		}

		return "UPDATE ".$this->escape_table($table)." SET ".implode(', ', $valstr)." WHERE ".implode(" ", $where);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	array	the where clause
	 * @return	string
	 */
	function _delete($table, $where)
	{
		return "DELETE FROM ".$this->escape_table($table)." WHERE ".implode(" ", $where);
	}

	// --------------------------------------------------------------------

	/**
	 * Show table query
	 *
	 * Generates a platform-specific query string so that the table names can be fetched
	 *
	 * @access	public
	 * @return	string
	 */
	function _show_tables()
	{
		return "SELECT name from sqlite_master WHERE type='table'";
	}

	// --------------------------------------------------------------------

	/**
	 * Show columnn query
	 *
	 * Generates a platform-specific query string so that the column names can be fetched
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	string
	 */
	function _show_columns($table = '')
	{
		// Not supported
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Limit string
	 *
	 * Generates a platform-specific LIMIT clause
	 *
	 * @access	public
	 * @param	string	the sql query string
	 * @param	integer	the number of rows to limit the query to
	 * @param	integer	the offset value
	 * @return	string
	 */
	function _limit($sql, $limit, $offset)
	{
		if ($offset == 0)
		{
			$offset = '';
		}
		else
		{
			$offset .= ", ";
		}

		return $sql."LIMIT ".$offset.$limit;
	}

// Commented by Xi
/**
     * COPY FROM sqlite_driver.php
     * Protect Identifiers ... contributed/requested by CodeIgniter user: quindo
     *
     * This function adds backticks if appropriate based on db type
     *
     * @access  private
     * @param   mixed   the item to escape
     * @param   boolean only affect the first word
     * @return  mixed   the item with backticks
     */
/*
    function _protect_identifiers($item, $first_word_only = FALSE)
    {
        if (is_array($item))
        {
            $escaped_array = array();

            foreach($item as $k=>$v)
            {
                $escaped_array[$this->_protect_identifiers($k)] = $this->_protect_identifiers($v, $first_word_only);
            }

            return $escaped_array;
        }

        // This function may get "item1 item2" as a string, and so
        // we may need "item1 item2" and not "item1 item2"
        if (ctype_alnum($item) === FALSE)
        {
            if (strpos($item, '.') !== FALSE)
            {
                $aliased_tables = implode(".",$this->ar_aliased_tables).'.';
                $table_name =  substr($item, 0, strpos($item, '.')+1);
                $item = (strpos($aliased_tables, $table_name) !== FALSE) ? $item = $item : $this->dbprefix.$item;
            }

            // This function may get "field >= 1", and need it to return "field >= 1"
            $lbound = ($first_word_only === TRUE) ? '' : '|\s|\(';

            $item = preg_replace('/(^'.$lbound.')([\w\d\-\_]+?)(\s|\)|$)/iS', '$1$2$3', $item);
        }
        else
        {
            return "{$item}";
        }

        $exceptions = array('AS', '/', '-', '%', '+', '*');

        foreach ($exceptions as $exception)
        {
            if (stristr($item, " {$exception} ") !== FALSE)
            {
                $item = preg_replace('/ ('.preg_quote($exception).') /i', ' $1 ', $item);
            }
        }
        return $item;
    }
*/


/**
     * From Tables ... contributed/requested by CodeIgniter user: quindo
     *
     * This function implicitly groups FROM tables so there is no confusion
     * about operator precedence in harmony with SQL standards
     *
     * @access  public
     * @param   type
     * @return  type
     */
    function _from_tables($tables)
    {
        if (! is_array($tables))
        {
            $tables = array($tables);
        }

        return implode(', ', $tables);
    }

// --------------------------------------------------------------------

    /**
     * Set client character set
     * contributed/requested by CodeIgniter user:  jtiai
     *
     * @access    public
     * @param    string
     * @param    string
     * @return    resource
     */
    function db_set_charset($charset, $collation)
    {
        // TODO - add support if needed
        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Close DB Connection
     *
     * @access    public
     * @param    resource
     * @return    void
     */
    function _close($conn_id)
    {
        // Do nothing since PDO don't have close
    }


/**
* List table query    
*    
* Generates a platform-specific query string so that the table names can be fetched    
*    
* @access      private    
* @param       boolean    
* @return      string    
*/   
function _list_tables($prefix_limit = FALSE)   
{
 $sql = "SELECT name from sqlite_master WHERE type='table'";           

 if ($prefix_limit !== FALSE AND $this->dbprefix != '')
  {
   $sql .= " AND 'name' LIKE '".$this->dbprefix."%'";
  }
 
 return $sql;
}


}

?>