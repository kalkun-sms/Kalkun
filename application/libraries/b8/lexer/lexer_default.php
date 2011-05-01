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
 * Copyright (C) 2006-2010 Tobias Leupold <tobias.leupold@web.de>
 *
 * @license LGPL
 * @access public
 * @package b8
 * @author Tobias Leupold
 * @author Oliver Lillie (aka buggedcom) (original PHP 5 port)
 */

class b8_lexer_default
{

	const LEXER_TEXT_NOT_STRING = 'LEXER_TEXT_NOT_STRING';
	const LEXER_TEXT_EMPTY      = 'LEXER_TEXT_EMPTY';

	public $config = NULL;

	# The regular expressions we use to split the text to tokens

	public $regexp = array(
		'ip'        => '/([A-Za-z0-9\_\-\.]+)/',
		'raw_split' => '/[\s,\.\/"\:;\|<>\-_\[\]{}\+=\)\(\*\&\^%]+/',
		'html'      => '/(<.+?>)/',
		'tagname'   => '/(.+?)\s/',
		'numbers'   => '/^[0-9]+$/'
	);

	/**
	 * Constructs the lexer.
	 *
	 * @access public
	 * @return void
	 */

	function __construct($config)
	{
		$this->config = $config;
	}

	/**
	 * Generates the tokens required for the bayesian filter.
	 *
	 * @access public
	 * @param string $text
	 * @return array Returns the list of tokens
	 */

	public function get_tokens($text)
	{

		# Check that we actually have a string ...
		if(is_string($text) === FALSE)
			return self::LEXER_TEXT_NOT_STRING;

		# ... and that it's not empty
		if(empty($text) === TRUE)
			return self::LEXER_TEXT_EMPTY;

		# Re-convert the text to the original characters coded in UTF-8, as
		# they have been coded in html entities during the post process
		$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

		$tokens = array();

		# Find URLs and IP addresses

		preg_match_all($this->regexp['ip'], $text, $raw_tokens);

		foreach($raw_tokens[1] as $word) {

			# Check for a dot
			if(strpos($word, '.') === FALSE)
				continue;

			# Check that the word is valid, min and max sizes, etc.
			if($this->_is_valid($word) === FALSE)
				continue;

			if(isset($tokens[$word]) === FALSE)
				$tokens[$word] = 1;
			else
				$tokens[$word] += 1;

			# Delete the word from the text so it doesn't get re-added.
			$text = str_replace($word, '', $text);

			# Also process the parts of the URLs
			$url_parts = preg_split($this->regexp['raw_split'], $word);

			foreach($url_parts as $word) {

				# Again validate the part

				if($this->_is_valid($word) === FALSE)
					continue;

				if(isset($tokens[$word]) === FALSE)
					$tokens[$word] = 1;
				else
					$tokens[$word] += 1;

			}

		}

		# Split the remaining text

		$raw_tokens = preg_split($this->regexp['raw_split'], $text);

		foreach($raw_tokens as $word) {

			# Again validate the part

			if($this->_is_valid($word) === FALSE)
				continue;

			if(isset($tokens[$word]) === FALSE)
				$tokens[$word] = 1;
			else
				$tokens[$word] += 1;

		}

		# Process the HTML

		preg_match_all($this->regexp['html'], $text, $raw_tokens);

		foreach($raw_tokens[1] as $word) {

			# Again validate the part

			if($this->_is_valid($word) === FALSE)
				continue;

			# If the tag has parameters, just use the tag itself

			if(strpos($word, ' ') !== FALSE) {
				preg_match($this->regexp['tagname'], $word, $tmp);
				$word = "{$tmp[1]}...>";
			}

			if(isset($tokens[$word]) === FALSE)
				$tokens[$word] = 1;
			else
				$tokens[$word] += 1;

		}

		# Return a list of all found tokens
		return $tokens;

	}

	/**
	 * Validates a token.
	 *
	 * @access private
	 * @param string $token The token string.
	 * @return boolean Returns TRUE if the token is valid, otherwise returns FALSE
	 */

	private function _is_valid($token)
	{

		# Validate the size of the token

		$len = strlen($token);

		if($len < $this->config['min_size'] or $len > $this->config['max_size'])
			return FALSE;

		# We may want to exclude pure numbers
		if($this->config['allow_numbers'] === FALSE) {
			if(preg_match($this->regexp['numbers'], $token) > 0)
				return FALSE;
		}

		# Token is okay
		return TRUE;

	}

}

?>