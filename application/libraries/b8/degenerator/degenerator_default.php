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
 */

class b8_degenerator_default
{

	public $degenerates = array();

	/**
	 * Generates a list of "degenerated" words for a list of words.
	 *
	 * @access public
	 * @param array $tokens
	 * @return array An array containing an array of degenerated tokens for each token
	 */

	public function degenerate(array $words)
	{

		$degenerates = array();

		foreach($words as $word)
			$degenerates[$word] = $this->_degenerate_word($word);

		return $degenerates;

	}

	/**
	 * If the original word is not found in the database then
	 * we build "degenerated" versions of the word to lookup.
	 *
	 * @access private
	 * @param string $word
	 * @return array An array of degenerated words
	 */

	protected function _degenerate_word($word)
	{

		# Check for any stored words so the process doesn't have to repeat
		if(isset($this->degenerates[$word]) === TRUE)
			return $this->degenerates[$word];

		$degenerate = array();

		# Add different version of upper and lower case and ucfirst
		array_push($degenerate, strtolower($word));
		array_push($degenerate, strtoupper($word));
		array_push($degenerate, ucfirst($word));

		# Degenerate all versions

		foreach($degenerate as $alt_word) {

			# Look for stuff like !!! and ???

			if(preg_match('/[!?]$/', $alt_word) > 0) {

				# Add versions with different !s and ?s

				if(preg_match('/[!?]{2,}$/', $alt_word) > 0) {
					$tmp = preg_replace('/([!?])+$/', '$1', $alt_word);
					array_push($degenerate, $tmp);
				}

				$tmp = preg_replace('/([!?])+$/', '', $alt_word);
				array_push($degenerate, $tmp);

			}

			# Look for ... at the end of the word

			$alt_word_int = $alt_word;

			while(preg_match('/[\.]$/', $alt_word_int) > 0) {
				$alt_word_int = substr($alt_word_int, 0, strlen($alt_word_int) - 1);
				array_push($degenerate, $alt_word_int);
			}

		}

		# Some degenerates are the same as the original word. These don't have
		# to be fetched, so we create a new array with only new tokens

		$real_degenerate = array();

		foreach($degenerate as $deg_word) {
			if($word != $deg_word)
				array_push($real_degenerate, $deg_word);
		}

		# Store the list of degenerates for the token
		$this->degenerates[$word] = $real_degenerate;

		return $real_degenerate;

	}

}

?>