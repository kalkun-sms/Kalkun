<?php

#   Copyright (C) 2006-2010 Tobias Leupold <tobias.leupold@web.de>
#
#   b8 - A Bayesian spam filter written in PHP 5
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
 * Copyright (C) 2006-2010 Tobias Leupold <tobias.leupold@web.de>
 *
 * @license LGPL
 * @access public
 * @package b8
 * @author Tobias Leupold
 * @author Oliver Lillie (aka buggedcom) (original PHP 5 port)
 */

class b8
{

	public $config = array(
		'min_size'      => 3,
		'max_size'      => 30,
		'allow_numbers' => FALSE,
		'lexer'         => 'default',
		'degenerator'   => 'default',
		'storage'       => 'dba',
		'use_relevant'  => 15,
		'min_dev'       => 0.2,
		'rob_s'         => 0.3,
		'rob_x'         => 0.5
	);

	private $_lexer      = NULL;
	private $_database   = NULL;
	private $_token_data = NULL;

	const SPAM    = 'spam';
	const HAM     = 'ham';
	const LEARN   = 'learn';
	const UNLEARN = 'unlearn';

	const STARTUP_FAIL_DATABASE = 'STARTUP_FAIL_DATABASE';
	const STARTUP_FAIL_LEXER    = 'STARTUP_FAIL_LEXER';
	const TRAINER_CATEGORY_FAIL = 'TRAINER_CATEGORY_FAIL';

	/**
	 * Constructs b8
	 *
	 * @access public
	 * @return void
	 */

	function __construct($config = array(), $database_config)
	{

		# Validate config data

		if(count($config) > 0) {

			foreach ($config as $name=>$value) {

				switch($name) {

					case 'min_dev':
					case 'rob_s':
					case 'rob_x':
						$this->config[$name] = (float) $value;
						break;

					case 'min_size':
					case 'max_size':
					case 'use_relevant':
						$this->config[$name] = (int) $value;
						break;

					case 'allow_numbers':
						$this->config[$name] = (bool) $value;
						break;

					case 'lexer':
						$value = (string) strtolower($value);
						$this->config[$name] = is_file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lexer' . DIRECTORY_SEPARATOR . "lexer_" . $value . '.php') === TRUE ? $value : 'default';
						break;

					case 'storage':
						$this->config[$name] = (string) $value;
						break;

				}

			}

		}

		# Setup the database backend

		# Get the basic storage class used by all backends
		if($this->load_class('b8_storage_base', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'storage_base.php') === FALSE)
			return;

		# Get the degenerator we need
		if($this->load_class('b8_degenerator_' . $this->config['degenerator'], dirname(__FILE__) . DIRECTORY_SEPARATOR . 'degenerator' . DIRECTORY_SEPARATOR . 'degenerator_' . $this->config['degenerator'] . '.php') === FALSE)
			return;

		# Get the actual storage backend we need
		if($this->load_class('b8_storage_' . $this->config['storage'], dirname(__FILE__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'storage_' . $this->config['storage'] . '.php') === FALSE)
			return;

		# Setup the backend
		$class = 'b8_storage_' . $this->config['storage'];
		$this->_database = new $class(
			$database_config,
			$this->config['degenerator'], date('ymd')
		);

		# Setup the lexer class

		if($this->load_class('b8_lexer_' . $this->config['lexer'], dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lexer' . DIRECTORY_SEPARATOR . 'lexer_' . $this->config['lexer'] . '.php') === FALSE)
			return;

		$class = 'b8_lexer_' . $this->config['lexer'];
		$this->_lexer = new $class(
			array(
				'min_size' => $this->config['min_size'],
				'max_size' => $this->config['max_size'],
				'allow_numbers' => $this->config['allow_numbers']
			)
		);

	}

	/**
	 * Load a class file if a class has not been defined yet.
	 *
	 * @access public
	 * @return boolean Returns TRUE if everything is okay, otherwise FALSE.
	 */

	public function load_class($class_name, $class_file)
	{

		if(class_exists($class_name, FALSE) === FALSE) {

			$included = require_once $class_file;

			if($included === FALSE or class_exists($class_name, FALSE) === FALSE)
				return FALSE;

		}

		return TRUE;

	}

	/**
	 * Validates the class has all it needs to work.
	 *
	 * @access public
	 * @return mixed Returns TRUE if everything is okay, otherwise an error code.
	 */

	public function validate()
	{

		if($this->_database === NULL)
			return self::STARTUP_FAIL_DATABASE;

		# Connect the database backend if we aren't connected yet

		elseif($this->_database->connected === FALSE) {

			$connection = $this->_database->connect();

			if($connection !== TRUE)
				return $connection;

		}

		if($this->_lexer === NULL)
			return self::STARTUP_FAIL_LEXER;

		return TRUE;

	}

	/**
	 * Classifies a text
	 *
	 * @access public
	 * @package default
	 * @param string $text
	 * @return float The rating between 0 (ham) and 1 (spam)
	 */

	public function classify($text)
	{

		# Validate the startup

		$started_up = $this->validate();

		if($started_up !== TRUE)
			return $started_up;

		# Get the internal database variables, containing the number of ham and
		# spam texts so the spam probability can be calculated in relation to them
		$internals = $this->_database->get_internals();

		# Calculate the spamminess of all tokens

		# Get all tokens we want to rate

		$tokens = $this->_lexer->get_tokens($text);

		# Check if the lexer failed
		# (if so, $tokens will be a lexer error code, if not, $tokens will be an array)
		if(!is_array($tokens))
			return $tokens;

		# Fetch all availible data for the token set from the database
		$this->_token_data = $this->_database->get(array_keys($tokens));

		# Calculate the spamminess and importance for each token (or a degenerated form of it)

		$word_count = array();
		$rating     = array();
		$importance = array();

		foreach($tokens as $word => $count) {

			$word_count[$word] = $count;

			# Although we only call this function only here ... let's do the
			# calculation stuff in a function to make this a bit less confusing ;-)
			$rating[$word] = $this->_get_probability($word, $internals['texts_ham'], $internals['texts_spam']);

			$importance[$word] = abs(0.5 - $rating[$word]);

		}

		# Order by importance
		arsort($importance);
		reset($importance);

		# Get the most interesting tokens (use all if we have less than the given number)

		$relevant = array();

		for($i = 0; $i < $this->config['use_relevant']; $i++) {

			if($tmp = each($importance)) {

				# Important tokens remain

				# If the token's rating is relevant enough, use it

				if(abs(0.5 - $rating[$tmp['key']]) > $this->config['min_dev']) {

					# Tokens that appear more than once also count more than once

					for($x = 0, $l = $word_count[$tmp['key']]; $x < $l; $x++)
						array_push($relevant, $rating[$tmp['key']]);

				}

			}

			else {
				# We have less than words to use, so we already
				# use what we have and can break here
				break;
			}

		}

		# Calculate the spamminess of the text (thanks to Mr. Robinson ;-)
		# We set both hamminess and Spamminess to 1 for the first multiplying
		$hamminess  = 1;
		$spamminess = 1;

		# Consider all relevant ratings
		foreach($relevant as $value) {
			$hamminess  *= (1.0 - $value);
			$spamminess *= $value;
		}

		# If no token was good for calculation, we really don't know how
		# to rate this text; so we assume a spam and ham probability of 0.5

		if($hamminess === 1 and $spamminess === 1) {
			$hamminess = 0.5;
			$spamminess = 0.5;
			$n = 1;
		}
		else {
			# Get the number of relevant ratings
			$n = count($relevant);
		}

		# Calculate the combined rating

		# The actual hamminess and spamminess
		$hamminess  = 1 - pow($hamminess,  (1 / $n));
		$spamminess = 1 - pow($spamminess, (1 / $n));

		# Calculate the combined indicator
		$probability = ($hamminess - $spamminess) / ($hamminess + $spamminess);

		# We want a value between 0 and 1, not between -1 and +1, so ...
		$probability = (1 + $probability) / 2;

		# Alea iacta est
		return $probability;

	}

	/**
	 * Calculate the spamminess of a single token also considering "degenerated" versions
	 *
	 * @access private
	 * @param string $word
	 * @param string $texts_ham
	 * @param string $texts_spam
	 * @return void
	 */

	private function _get_probability($word, $texts_ham, $texts_spam)
	{

		# Let's see what we have!

		if(isset($this->_token_data['tokens'][$word]) === TRUE) {
			# The token was in the database, so we can use it's data as-is
			# and calculate the spamminess of this token directly
			return $this->_calc_probability($this->_token_data['tokens'][$word], $texts_ham, $texts_spam);
		}

		# Damn. The token was not found, so do we have at least similar words?

		if(isset($this->_token_data['degenerates'][$word]) === TRUE) {

			# We found similar words, so calculate the spamminess for each one
			# and choose the most important one for the further calculation

			# The default rating is 0.5 simply saying nothing
			$rating = 0.5;

			foreach($this->_token_data['degenerates'][$word] as $degenerate => $count) {

				# Calculate the rating of the current degenerated token
				$rating_tmp = $this->_calc_probability($count, $texts_ham, $texts_spam);

				# Is it more important than the rating of another degenerated version?
				if(abs(0.5 - $rating_tmp) > abs(0.5 - $rating))
					$rating = $rating_tmp;

			}

			return $rating;

		}

		else {
			# The token is really unknown, so choose the default rating
			# for completely unknown tokens. This strips down to the
			# robX parameter so we can cheap out the freaky math ;-)
			return $this->config['rob_x'];
		}

	}

	/**
	 * Do the actual spamminess calculation of a single token
	 *
	 * @access private
	 * @param array $data
	 * @param string $texts_ham
	 * @param string $texts_spam
	 * @return void
	 */

	private function _calc_probability($data, $texts_ham, $texts_spam)
	{

		# Calculate the basic probability by Mr. Graham

		# But: consider the number of ham and spam texts saved instead of the
		# number of entries where the token appeared to calculate a relative
		# spamminess because we count tokens appearing multiple times not just
		# once but as often as they appear in the learned texts

		$rel_ham = $data['count_ham'];
		$rel_spam = $data['count_spam'];

		if($texts_ham > 0)
			$rel_ham = $data['count_ham'] / $texts_ham;

		if($texts_spam > 0)
			$rel_spam = $data['count_spam'] / $texts_spam;

		$rating = $rel_spam / ($rel_ham + $rel_spam);

		# Calculate the better probability proposed by Mr. Robinson
		$all = $data['count_ham'] + $data['count_spam'];
		return (($this->config['rob_s'] * $this->config['rob_x']) + ($all * $rating)) / ($this->config['rob_s'] + $all);

	}

	/**
	 * Check the validity of the category of a request
	 *
	 * @access private
	 * @param string $category
	 * @return void
	 */

	private function _check_category($category)
	{
		return $category === self::HAM or $category === self::SPAM;
	}

	/**
	 * Learn a reference text
	 *
	 * @access public
	 * @param string $text
	 * @param const $category Either b8::SPAM or b8::HAM
	 * @return void
	 */

	public function learn($text, $category)
	{
		return $this->_process_text($text, $category, self::LEARN);
	}

	/**
	 * Unlearn a reference text
	 *
	 * @access public
	 * @param string $text
	 * @param const $category Either b8::SPAM or b8::HAM
	 * @return void
	 */

	public function unlearn($text, $category)
	{
		return $this->_process_text($text, $category, self::UNLEARN);
	}

	/**
	 * Does the actual interaction with the storage backend for learning or unlearning texts
	 *
	 * @access private
	 * @param string $text
	 * @param const $category Either b8::SPAM or b8::HAM
	 * @param const $action Either b8::LEARN or b8::UNLEARN
	 * @return void
	 */

	private function _process_text($text, $category, $action)
	{

		# Validate the startup

		$started_up = $this->validate();

		if($started_up !== TRUE)
			return $started_up;

		# Look if the request is okay
		if($this->_check_category($category) === FALSE)
			return self::TRAINER_CATEGORY_FAIL;

		# Get all tokens from $text

		$tokens = $this->_lexer->get_tokens($text);

		# Check if the lexer failed
		# (if so, $tokens will be a lexer error code, if not, $tokens will be an array)
		if(!is_array($tokens))
			return $tokens;

		# Pass the tokens and what to do with it to the storage backend
		return $this->_database->process_text($tokens, $category, $action);

	}

}

?>