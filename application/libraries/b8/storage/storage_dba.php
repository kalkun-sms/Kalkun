<?php

#   Copyright (C) 2006-2010 Tobias Leupold <tobias.leupold@web.de>
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
 * The DBA (Berkeley DB) abstraction layer for communicating with the database.
 * Copyright (C) 2006-2010 Tobias Leupold <tobias.leupold@web.de>
 *
 * @license LGPL
 * @access public
 * @package b8
 * @author Tobias Leupold
 */

class b8_storage_dba extends b8_storage_base
{

	public $config = array(
		'database'    => 'wordlist.db',
		'handler'     => 'db4',
	);

	public $b8_config = array(
		'degenerator' => NULL,
		'today'       => NULL
	);

	private $_db          = NULL;

	const DATABASE_CONNECTION_FAIL = 'DATABASE_CONNECTION_FAIL';

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
		$this->b8_config['today']       = $today;

		# Validate the config items
		if(count($config) > 0) {
			foreach ($config as $name => $value) {
				$this->config[$name] = (string) $value;
			}
		}

	}

	/**
	 * Closes the database connection.
	 *
	 * @access public
	 * @return void
	 */

	function __destruct()
	{
		if($this->_db !== NULL) {
			dba_close($this->_db);
			$this->connected = FALSE;
		}
	}

	/**
	 * Connect to the database and do some checks.
	 *
	 * @access public
	 * @return mixed Returns TRUE on a successful database connection, otherwise returns a constant from b8.
	 */

	public function connect()
	{

		# Have we already connected?
		if($this->_db !== NULL)
			return TRUE;

		# Open the database connection
		$this->_db = dba_open(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . $this->config['database'], "w", $this->config['handler']);

		if($this->_db === FALSE) {
			$this->connected = FALSE;
			$this->_db = NULL;
			return self::DATABASE_CONNECTION_FAIL;
		}

		# Everything is okay and connected

		$this->connected = TRUE;

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

		$data = array();

		foreach ($tokens as $token) {

			$count = dba_fetch($token, $this->_db);

			if($count !== FALSE)
				$data[$token] = $count;

		}

		return $data;

	}

	/**
	 * Store a token to the database.
	 *
	 * @access protected
	 * @param string $token
	 * @param string $count
	 * @return bool TRUE on success or FALSE on failure
	 */

	protected function _put($token, $count) {
		return dba_insert($token, $count, $this->_db);
	}

	/**
	 * Update an existing token.
	 *
	 * @access protected
	 * @param string $token
	 * @param string $count
	 * @return bool TRUE on success or FALSE on failure
	 */

	protected function _update($token, $count)
	{
		return dba_replace($token, $count, $this->_db);
	}

	/**
	 * Remove a token from the database.
	 *
	 * @access protected
	 * @param string $token
	 * @return bool TRUE on success or FALSE on failure
	 */

	protected function _del($token)
	{
		return dba_delete($token, $this->_db);
	}

	/**
	 * Does nothing :-D
	 *
	 * @access protected
	 * @return void
	 */

	protected function _commit()
	{
		# We just need this function because the (My)SQL backend(s) need it.
		return;
	}

}

?>