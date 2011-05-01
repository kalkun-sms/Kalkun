<?php

#   Copyright (C) 2010 Tobias Leupold <tobias.leupold@web.de>
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
 * Functions used by all storage backends
 * Copyright (C) 2010 Tobias Leupold <tobias.leupold@web.de>
 *
 * @license LGPL
 * @access public
 * @package b8
 * @author Tobias Leupold
 */

abstract class b8_storage_base
{

	public $connected            = FALSE;

	protected $_degenerator      = NULL;

	const INTERNALS_TEXTS_HAM    = 'bayes*texts.ham';
	const INTERNALS_TEXTS_SPAM   = 'bayes*texts.spam';
	const INTERNALS_DBVERSION    = 'bayes*dbversion';

	const BACKEND_NOT_CONNECTED  = 'BACKEND_NOT_CONNECTED';
	const DATABASE_WRONG_VERSION = 'DATABASE_WRONG_VERSION';
	const DATABASE_NOT_B8        = 'DATABASE_NOT_B8';

	/**
	 * Validates the class has all it needs to work.
	 *
	 * @access protected
	 * @return mixed Returns TRUE if everything is okay, otherwise an error code.
	 */

	protected function validate()
	{

		# We set up the degenerator here, as we would have to duplicate code if it
		# was done in the constructor of the respective storage backend.
		$class = 'b8_degenerator_' . $this->b8_config['degenerator'];
		$this->_degenerator = new $class();

		if($this->connected !== TRUE)
			return self::BACKEND_NOT_CONNECTED;

		return TRUE;

	}

	/**
	 * Checks if a b8 database is used and if it's version is okay
	 *
	 * @access protected
	 * @return mixed Returns TRUE if everything is okay, otherwise an error code.
	 */

	protected function check_database()
	{

		$internals = $this->get_internals();

		if(isset($internals['dbversion'])) {
			if($internals['dbversion'] == "2") {
				return TRUE;
			}
			else {
				$this->connected = FALSE;
				return self::DATABASE_WRONG_VERSION;
			}
		}
		else {
			$this->connected = FALSE;
			return self::DATABASE_NOT_B8;
		}

	}

	/**
	 * Parses the "count" data of a token.
	 *
	 * @access private
	 * @param string $data
	 * @return array Returns an array of the parsed data: array(count_ham, count_spam, lastseen).
	 */

	private function _parse_count($data)
	{

		list($count_ham, $count_spam, $lastseen) = explode(' ', $data);

		$count_ham  = (int) $count_ham;
		$count_spam = (int) $count_spam;

		return array(
			'count_ham'  => $count_ham,
			'count_spam' => $count_spam
		);

	}

	/**
	 * Get the database's internal variables.
	 *
	 * @access public
	 * @return array Returns an array of all internals.
	 */

	public function get_internals()
	{

		$internals = $this->_get_query(
			array(
				self::INTERNALS_TEXTS_HAM,
				self::INTERNALS_TEXTS_SPAM,
				self::INTERNALS_DBVERSION
			)
		);

		return array(
			'texts_ham'  => (int) $internals[self::INTERNALS_TEXTS_HAM],
			'texts_spam' => (int) $internals[self::INTERNALS_TEXTS_SPAM],
			'dbversion'  => (int) $internals[self::INTERNALS_DBVERSION]
		);

	}

	/**
	 * Get all data about a list of tags from the database.
	 *
	 * @access public
	 * @param array $tokens
	 * @return mixed Returns FALSE on failure, otherwise returns array of returned data in the format array('tokens' => array(token => count), 'degenerates' => array(token => array(degenerate => count))).
	 */

	public function get($tokens)
	{

		# Validate the startup

		$started_up = $this->validate();

		if($started_up !== TRUE)
			return $started_up;

		# First we see what we have in the database.
		$token_data = $this->_get_query($tokens);

		# Check if we have to degenerate some tokens

		$missing_tokens = array();

		foreach($tokens as $token) {
			if(!isset($token_data[$token]))
				$missing_tokens[] = $token;
		}

		if(count($missing_tokens) > 0) {

			# We have to degenerate some tokens
			$degenerates_list = array();

			# Generate a list of degenerated tokens for the missing tokens ...
			$degenerates = $this->_degenerator->degenerate($missing_tokens);

			# ... and look them up

			foreach($degenerates as $token => $token_degenerates)
				$degenerates_list = array_merge($degenerates_list, $token_degenerates);

			$token_data = array_merge($token_data, $this->_get_query($degenerates_list));

		}

		# Here, we have all availible data in $token_data.

		$return_data_tokens = array();
		$return_data_degenerates = array();

		foreach($tokens as $token) {

			if(isset($token_data[$token]) === TRUE) {

				# The token was found in the database

				# Add the data ...
				$return_data_tokens[$token] = $this->_parse_count($token_data[$token]);

				# ... and update it's lastseen parameter
				$this->_update($token, "{$return_data_tokens[$token]['count_ham']} {$return_data_tokens[$token]['count_spam']} " . $this->b8_config['today']);

			}

			else {

				# The token was not found, so we look if we
				# can return data for degenerated tokens

				# Check all degenerated forms of the token

				foreach($this->_degenerator->degenerates[$token] as $degenerate) {

					if(isset($token_data[$degenerate]) === TRUE) {

						# A degeneration of the token way found in the database

						# Add the data ...
						$return_data_degenerates[$token][$degenerate] = $this->_parse_count($token_data[$degenerate]);

						# ... and update it's lastseen parameter
						$this->_update($degenerate, "{$return_data_degenerates[$token][$degenerate]['count_ham']} {$return_data_degenerates[$token][$degenerate]['count_spam']} " . $this->b8_config['today']);

					}

				}

			}

		}

		# Now, all token data directly found in the database is in $return_data_tokens
		# and all data for degenerated versions is in $return_data_degenerates

		# First, we commit the changes to the lastseen parameters
		$this->_commit();

		# Then, we return what we have
		return array(
			'tokens'      => $return_data_tokens,
			'degenerates' => $return_data_degenerates
		);

	}

	/**
	 * Stores or deletes a list of tokens from the given category.
	 *
	 * @access public
	 * @param array $tokens
	 * @param const $category Either b8::HAM or b8::SPAM
	 * @param const $action Either b8::LEARN or b8::UNLEARN
	 * @return void
	 */

	public function process_text($tokens, $category, $action)
	{

		# Validate the startup

		$started_up = $this->validate();

		if($started_up !== TRUE)
			return $started_up;

		# No matter what we do, we first have to check what data we have.

		# First get the internals, including the ham texts and spam texts counter
		$internals = $this->get_internals();

		# Then, fetch all data for all tokens we have (and update their lastseen parameters)
		$token_data = $this->_get_query(array_keys($tokens));

		# Process all tokens to learn/unlearn

		foreach($tokens as $token => $count) {

			if(isset($token_data[$token])) {

				# We already have this token, so update it's data

				# Get the existing data
				list($count_ham, $count_spam, $lastseen) = explode(' ', $token_data[$token]);
				$count_ham  = (int) $count_ham;
				$count_spam = (int) $count_spam;

				# Increase or decrease the right counter

				if($action === b8::LEARN) {
					if($category === b8::HAM)
						$count_ham += $count;
					elseif($category === b8::SPAM)
						$count_spam += $count;
				}

				elseif($action == b8::UNLEARN) {
					if($category === b8::HAM)
						$count_ham -= $count;
					elseif($category === b8::SPAM)
						$count_spam -= $count;
				}

				# We don't want to have negative values

				if($count_ham < 0)
					$count_ham = 0;

				if($count_spam < 0)
					$count_spam = 0;

				# Now let's see if we have to update or delete the token
				if($count_ham !== 0 or $count_spam !== 0)
					$this->_update($token, "$count_ham $count_spam " . $this->b8_config['today']);
				else
					$this->_del($token);

			}

			else {

				# We don't have the token. If we unlearn a text, we can't delete it
				# as we don't have it anyway, so just do something if we learn a text

				if($action === b8::LEARN) {

					if($category === b8::HAM)
						$data = '1 0 ';
					elseif($category === b8::SPAM)
						$data = '0 1 ';

					$data .= $this->b8_config['today'];

					$this->_put($token, $data);

				}

			}

		}

		# Now, all token have been processed, so let's update the right text

		if($action === b8::LEARN) {

			if($category === b8::HAM) {
				$internals['texts_ham']++;
				$this->_update(self::INTERNALS_TEXTS_HAM, $internals['texts_ham']);
			}

			elseif($category === b8::SPAM) {
				$internals['texts_spam']++;
				$this->_update(self::INTERNALS_TEXTS_SPAM, $internals['texts_spam']);
			}

		}

		elseif($action == b8::UNLEARN) {

			if($category === b8::HAM) {

				$internals['texts_ham']--;

				if($internals['texts_ham'] < 0)
					$internals['texts_ham'] = 0;

				$this->_update(self::INTERNALS_TEXTS_HAM, $internals['texts_ham']);

			}

			elseif($category === b8::SPAM) {

				$internals['texts_spam']--;

				if($internals['texts_spam'] < 0)
					$internals['texts_spam'] = 0;

				$this->_update(self::INTERNALS_TEXTS_SPAM, $internals['texts_spam']);

			}

		}

		# We're done and can commit all changes to the database now
		$this->_commit();

	}

}

?>