<?php

#   Copyright (C) 2006-2011 Tobias Leupold <tobias.leupold@web.de>
#
#   This file is part of the b8 package
#
#   This program is free software; you can redistribute it and/or modify it
#   under the terms of the GNU Lesser General Public License as published by
#   the Free Software Foundation in version 2.1 of the License.
#
#   This program is distributed in the hope that it will be useful, but
#   WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
#   or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public
#   License for more details.
#
#   You should have received a copy of the GNU Lesser General Public License
#   along with this program; if not, write to the Free Software Foundation,
#   Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.

/**
 * The CodeIgniter abstraction layer for communicating with the database.
 * Copyright (C) 2009 Oliver Lillie (aka buggedcom)
 * Copyright (C) 2010-2011 Tobias Leupold <tobias.leupold@web.de>
 * Copyright (C) 2011 Kinshuk Bairagi <kinshuk1989@gmail.com>
 *
 * @license LGPL
 * @access public
 * @package Kalkun-b8
 * @author Kinshuk Bairagi (CodeIgniter Port)
 * @author Oliver Lillie (aka buggedcom) (original PHP 5 port and optimizations)
 * @author Tobias Leupold
 */

class b8_storage_active extends b8_storage_base
{

    public $b8_config = array('degenerator' => null, 'today' => null);

    private $_connection = null;
    private $_deletes = array();
    private $_puts = array();
    private $_updates = array();

    const DATABASE_CONNECTION_FAIL = 'DATABASE_CONNECTION_FAIL';
    const DATABASE_CONNECTION_ERROR = 'DATABASE_CONNECTION_ERROR';
    const DATABASE_CONNECTION_BAD_RESOURCE = 'DATABASE_CONNECTION_BAD_RESOURCE';
    const DATABASE_SELECT_ERROR = 'DATABASE_SELECT_ERROR';
    const DATABASE_TABLE_ACCESS_FAIL = 'DATABASE_TABLE_ACCESS_FAIL';
    const DATABASE_WRONG_VERSION = 'DATABASE_WRONG_VERSION';


    /**
     * Constructs the database layer.
     *
     * @access public
     * @param string $config
     */

    function __construct($config, $degenerator, $today)
    {

        # Pass some variables of the main b8 config to this class
        $this->b8_config['degenerator'] = $degenerator;
        $this->b8_config['today'] = $today;

    }

    /**
     * Closes the database connection.
     *
     * @access public
     * @return void
     */

    function __destruct()
    {

        if ($this->_connection === null)
            return;

        # Commit any changes before closing
        $this->_commit();

        # Just close the connection if no link-resource was passed and b8 created it's own connection
        #if($this->config['connection'] === NULL)
        #	$this->db->close();

        $this->connected = false;

    }

    /**
     * Connect to the database and do some checks.
     *
     * @access public
     * @return mixed Returns TRUE on a successful database connection, otherwise returns a constant from b8.
     */

    public function connect()
    {
        $CI = &get_instance();

        # Check to see if the wordlist table exists
        if (!$CI->db->table_exists('b8_wordlist')) {
            $this->connected = false;
            return self::DATABASE_TABLE_ACCESS_FAIL . ": " . mysql_error();
        }

        # Everything is okay and connected
        $this->connected = true;

        # Let's see if this is a b8 database and the version is okay
        return $this->check_database();

    }

    /**
     * Does the actual interaction with the database when fetching data.
     *
     * @access protected
     * @param array $tokens
     * @return mixed Returns an array of the returned data in the format array(token => data) or an empty array if there was no data.
     */

    protected function _get_query($tokens)
    {
        # ... and fetch the data
        $CI = &get_instance();
        
        # Construct the query ...
        if (count($tokens) > 0) {

            $where = array();

            foreach ($tokens as $token) {
                array_push($where, $token);
            }
			$CI->db->where_in('token', $where);
        } else {
            $CI->db->where('token', $token);
        }

        $CI->db->select('token, count');

        $res = $CI->db->get('b8_wordlist')->result_array();
        $data = array();

        foreach ($res as $row)
            $data[$row['token']] = $row['count'];

        return $data;

    }

    /**
     * Store a token to the database.
     *
     * @access protected
     * @param string $token
     * @param string $count
     * @return void
     */

    protected function _put($token, $count)
    {
        array_push($this->_puts, array('token' => $token, 'count' => $count));
    }

    /**
     * Update an existing token.
     *
     * @access protected
     * @param string $token
     * @param string $count
     * @return void
     */

    protected function _update($token, $count)
    {
        array_push($this->_updates, array('token' => $token, 'count' => $count));
    }

    /**
     * Remove a token from the database.
     *
     * @access protected
     * @param string $token
     * @return void
     */

    protected function _del($token)
    {
        array_push($this->_deletes, $token);
    }

    /**
     * Commits any modification queries.
     *
     * @access protected
     * @return void
     */

    protected function _commit()
    {

        $CI = &get_instance();

        if (count($this->_deletes) > 0) {
			$CI->db->where_in('token', $this->_deletes);
            $CI->db->delete('b8_wordlist');

            $this->_deletes = array();

        }

        if (count($this->_puts) > 0) {

            //$CI->db->insert_batch('b8_wordlist', $this->_puts); // code igniter has a problem with the inser_batch func
            foreach ($this->_puts as $inpt) 
                $CI->db->insert('b8_wordlist', $inpt);
            $this->_puts = array();

        }

        if (count($this->_updates) > 0) {

            //maybe use duplicate method
            foreach ($this->_updates as $updt) {
                $CI->db->from('b8_wordlist');
                $CI->db->where('token', $updt['token']);
                if ($CI->db->count_all_results()) 
                {
                    $CI->db->where('token', $updt['token']);
                    $CI->db->update('b8_wordlist', $updt);
                }
                else 
                {
                    $CI->db->insert('b8_wordlist', $updt);
                }
            }
            $this->_updates = array();
        }
    }
}

?>