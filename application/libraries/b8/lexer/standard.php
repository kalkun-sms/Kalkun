<?php

/* Copyright (C) 2006-2019 Tobias Leupold <tobias.leupold@gmx.de>

   This file is part of the b8 package

   This program is free software; you can redistribute it and/or modify it
   under the terms of the GNU Lesser General Public License as published by
   the Free Software Foundation in version 2.1 of the License.

   This program is distributed in the hope that it will be useful, but
   WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
   or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
   License for more details.

   You should have received a copy of the GNU Lesser General Public License
   along with this program; if not, write to the Free Software Foundation,
   Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
*/

/**
 * A helper class to disassemble a text to tokens
 *
 * @license LGPL 2.1
 * @package b8
 * @author Tobias Leupold <tobias.leupold@gmx.de>
 * @author Oliver Lillie <ollie@buggedcom.co.uk> (original PHP 5 port)
 */

namespace b8\lexer;

class standard
{
    const LEXER_TEXT_NOT_STRING = 'LEXER_TEXT_NOT_STRING';
    const LEXER_TEXT_EMPTY      = 'LEXER_TEXT_EMPTY';

    const LEXER_NO_TOKENS = 'b8*no_tokens';

    private $config = [ 'min_size'      => 3,
                        'max_size'      => 30,
                        'get_uris'      => true,
                        'get_html'      => true,
                        'get_bbcode'    => false,
                        'allow_numbers' => false ];

    private $tokens         = null;
    private $processed_text = null;

    // The regular expressions we use to split the text to tokens
    private $regexp = [ 'raw_split' => '/[\s,\.\/"\:;\|<>\-_\[\]{}\+=\)\(\*\&\^%]+/',
                        'ip'        => '/([A-Za-z0-9\_\-\.]+)/',
                        'uris'      => '/([A-Za-z0-9\_\-]*\.[A-Za-z0-9\_\-\.]+)/',
                        'html'      => '/(<.+?>)/',
                        'bbcode'    => '/(\[.+?\])/',
                        'tagname'   => '/(.+?)\s/',
                        'numbers'   => '/^[0-9]+$/' ];

    /**
     * Constructs the lexer.
     *
     * @access public
     * @param array $config The configuration: [ 'min_size'      => int,
     *                                           'max_size'      => int,
     *                                           'get_uris'      => bool,
     *                                           'get_html'      => bool,
     *                                           'get_bbcode'    => bool,
     *                                           'allow_numbers' => bool ]
     * @return void
     */
    function __construct(array $config)
    {
        // Validate config data
        foreach ($config as $name=>$value) {
            switch ($name) {
                case 'min_size':
                case 'max_size':
                    $this->config[$name] = (int) $value;
                    break;
                case 'allow_numbers':
                case 'get_uris':
                case 'get_html':
                case 'get_bbcode':
                    $this->config[$name] = (bool) $value;
                    break;
                default:
                    throw new \Exception(standard::class . ": Unknown configuration key: "
                                         . "\"$name\"");
            }
        }
    }

    /**
     * Splits a text to tokens.
     *
     * @access public
     * @param string $text The text to disassemble
     * @return mixed Returns a list of tokens or an error code
     */
    public function get_tokens(string $text)
    {
        // Check if we actually have a string ...
        if (is_string($text) === false) {
            return self::LEXER_TEXT_NOT_STRING;
        }

        // ... and if it's empty
        if (empty($text) === true) {
            return self::LEXER_TEXT_EMPTY;
        }

        // Re-convert the text to the original characters coded in UTF-8, as they have been coded in
        // html entities during the post process
        $this->processed_text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        // Reset the token list
        $this->tokens = array();

        if ($this->config['get_uris'] === true) {
            // Get URIs
            $this->get_uris($this->processed_text);
        }

        if ($this->config['get_html'] === true) {
            // Get HTML
            $this->get_markup($this->processed_text, $this->regexp['html']);
        }

        if ($this->config['get_bbcode'] === true) {
            // Get BBCode
            $this->get_markup($this->processed_text, $this->regexp['bbcode']);
        }

        // We always want to do a raw split of the (remaining) text, so:
        $this->raw_split($this->processed_text);

        // Be sure not to return an empty array
        if (count($this->tokens) == 0) {
            $this->tokens[self::LEXER_NO_TOKENS] = 1;
        }

        // Return a list of all found tokens
        return $this->tokens;
    }

    /**
     * Validates a token.
     *
     * @access private
     * @param string $token The token string
     * @return bool Returns true if the token is valid, otherwise returns false.
     */
    private function is_valid(string $token)
    {
        // Just to be sure that the token's name won't collide with b8's internal variables
        if (substr($token, 0, 3) == 'b8*') {
            return false;
        }

        // Validate the size of the token
        $len = strlen($token);
        if ($len < $this->config['min_size'] || $len > $this->config['max_size']) {
            return false;
        }

        // We may want to exclude pure numbers
        if ($this->config['allow_numbers'] === false
            && preg_match($this->regexp['numbers'], $token) > 0) {

            return false;
        }

        // Token is okay
        return true;
    }

    /**
     * Checks the validity of a token and adds it to the token list if it's valid.
     *
     * @access private
     * @param string $token
     * @param string $word_to_remove Word to remove from the processed string
     * @return void
     */
    private function add_token(string $token, string $word_to_remove = null)
    {
        // Check the validity of the token
        if (! $this->is_valid($token)) {
            return;
        }

        // Add it to the list or increase it's counter
        if (! isset($this->tokens[$token])) {
            $this->tokens[$token] = 1;
        } else {
            $this->tokens[$token] += 1;
        }

        // If requested, remove the word or it's original version from the text
        if ($word_to_remove !== null) {
            $this->processed_text = str_replace($word_to_remove, '', $this->processed_text);
        }
    }

    /**
     * Gets URIs.
     *
     * @access private
     * @param string $text
     * @return void
     */
    private function get_uris(string $text)
    {
        // Find URIs
        preg_match_all($this->regexp['uris'], $text, $raw_tokens);
        foreach ($raw_tokens[1] as $word) {
            // Remove a possible trailing dot
            $word = rtrim($word, '.');
            // Try to add the found tokens to the list
            $this->add_token($word, $word);
            // Also process the parts of the found URIs
            $this->raw_split($word);
        }
    }

    /**
     * Gets HTML or BBCode markup, depending on the regexp used.
     *
     * @access private
     * @param string $text
     * @param string $regexp
     * @return void
     */
    private function get_markup(string $text, string $regexp)
    {
        // Search for the markup
        preg_match_all($regexp, $text, $raw_tokens);
        foreach ($raw_tokens[1] as $word) {
            $actual_word = $word;

            // If the tag has parameters, just use the tag itself
            if (strpos($word, ' ') !== false) {
                preg_match($this->regexp['tagname'], $word, $match);
                $actual_word = $match[1];
                $word = "$actual_word..." . substr($word, -1);
            }

            // Try to add the found tokens to the list
            $this->add_token($word, $actual_word);
        }
    }

    /**
     * Does a raw split.
     *
     * @access private
     * @param string $text
     * @return void
     */
    private function raw_split(string $text)
    {
        foreach (preg_split($this->regexp['raw_split'], $text) as $word) {
            // Check the word and add it to the token list if it's valid
            $this->add_token($word);
        }
    }

}
