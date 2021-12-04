<?php

/* Copyright (C) 2006-2019 Tobias Leupold <tobias.leupold@gmx.de>

   b8 - A statistical ("Bayesian") spam filter written in PHP

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
 * The b8 spam filter library
 *
 * @license LGPL 2.1
 * @package b8
 * @author Tobias Leupold <tobias.leupold@gmx.de>
 * @author Oliver Lillie <ollie@buggedcom.co.uk> (original PHP 5 port)
 */

namespace b8;

spl_autoload_register(
    function ($class) {
        $parts = explode('\\', $class);
        require_once __DIR__ . DIRECTORY_SEPARATOR . $parts[1]
                     . DIRECTORY_SEPARATOR . $parts[2] . '.php';
    }
);

class b8
{
    const DBVERSION = 3;

    const SPAM    = 'spam';
    const HAM     = 'ham';
    const LEARN   = 'learn';
    const UNLEARN = 'unlearn';

    const CLASSIFIER_TEXT_MISSING = 'CLASSIFIER_TEXT_MISSING';

    const TRAINER_TEXT_MISSING     = 'TRAINER_TEXT_MISSING';
    const TRAINER_CATEGORY_MISSING = 'TRAINER_CATEGORY_MISSING';
    const TRAINER_CATEGORY_FAIL    = 'TRAINER_CATEGORY_FAIL';

    const INTERNALS_TEXTS     = 'b8*texts';
    const INTERNALS_DBVERSION = 'b8*dbversion';

    const KEY_DB_VERSION = 'dbversion';
    const KEY_COUNT_HAM  = 'count_ham';
    const KEY_COUNT_SPAM = 'count_spam';
    const KEY_TEXTS_HAM  = 'texts_ham';
    const KEY_TEXTS_SPAM = 'texts_spam';

    private $config = [ 'lexer'        => 'standard',
                        'degenerator'  => 'standard',
                        'storage'      => 'dba',
                        'use_relevant' => 15,
                        'min_dev'      => 0.2,
                        'rob_s'        => 0.3,
                        'rob_x'        => 0.5 ];

    private $storage     = null;
    private $lexer       = null;
    private $degenerator = null;
    private $token_data  = null;

    /**
     * Constructs b8
     *
     * @access public
     * @param array b8's configuration: [ 'lexer'        => string,
                                          'degenerator'  => string,
                                          'storage'      => string,
                                          'use_relevant' => int,
                                          'min_dev'      => float,
                                          'rob_s'        => float,
                                          'rob_x'        => float ]
     * @param array The storage backend's config (depending on the backend used)
     * @param array The lexer's config (depending on the lexer used)
     * @param array The degenerator's config (depending on the degenerator used)
     * @return void
     */
    function __construct(array $config             = [],
                         array $config_storage     = [],
                         array $config_lexer       = [],
                         array $config_degenerator = [])
    {
        // Validate config data
        foreach ($config as $name => $value) {
            switch ($name) {
                case 'min_dev':
                case 'rob_s':
                case 'rob_x':
                    $this->config[$name] = (float) $value;
                    break;
                case 'use_relevant':
                    $this->config[$name] = (int) $value;
                    break;
                case 'lexer':
                case 'degenerator':
                case 'storage':
                    $this->config[$name] = (string) $value;
                    break;
                default:
                    throw new \Exception(b8::class . ": Unknown configuration key: \"$name\"");
            }
        }

        // Setup the degenerator class
        $class = '\\b8\\degenerator\\' . $this->config['degenerator'];
        $this->degenerator = new $class($config_degenerator);

        // Setup the lexer class
        $class = '\\b8\\lexer\\' . $this->config['lexer'];
        $this->lexer = new $class($config_lexer);

        // Setup the storage backend
        $class = '\\b8\\storage\\' . $this->config['storage'];
        $this->storage = new $class($config_storage, $this->degenerator);
    }

    /**
     * Classifies a text
     *
     * @access public
     * @param string The text to classify
     * @return mixed float The rating between 0 (ham) and 1 (spam) or an error code
     */
    public function classify(string $text = null)
    {
        // Let's first see if the user called the function correctly
        if ($text === null) {
            return \b8\b8::CLASSIFIER_TEXT_MISSING;
        }

        // Get the internal database variables, containing the number of ham and spam texts so the
        // spam probability can be calculated in relation to them
        $internals = $this->storage->get_internals();

        // Calculate the spaminess of all tokens

        // Get all tokens we want to rate
        $tokens = $this->lexer->get_tokens($text);

        // Check if the lexer failed (if so, $tokens will be a lexer error code, if not, $tokens
        //  will be an array)
        if (! is_array($tokens)) {
            return $tokens;
        }

        // Fetch all available data for the token set from the database
        $this->token_data = $this->storage->get(array_keys($tokens));

        // Calculate the spaminess and importance for each token (or a degenerated form of it)

        $word_count = [];
        $rating     = [];
        $importance = [];

        foreach ($tokens as $word => $count) {
            $word_count[$word] = $count;

            // Although we only call this function only here ... let's do the calculation stuff in a
            // function to make this a bit less confusing ;-)
            $rating[$word] = $this->get_probability($word, $internals);
            $importance[$word] = abs(0.5 - $rating[$word]);
        }

        // Order by importance
        arsort($importance);
        reset($importance);

        // Get the most interesting tokens (use all if we have less than the given number)
        $relevant = [];
        for ($i = 0; $i < $this->config['use_relevant']; $i++) {
            if ($token = key($importance)) {
                // Important tokens remain

                // If the token's rating is relevant enough, use it
                if (abs(0.5 - $rating[$token]) > $this->config['min_dev']) {
                    // Tokens that appear more than once also count more than once
                    for ($x = 0, $l = $word_count[$token]; $x < $l; $x++) {
                        array_push($relevant, $rating[$token]);
                    }
                }
            } else {
                // We have less words as we want to use, so we already use what we have and can
                // break here
                break;
            }

            next($importance);
        }

        // Calculate the spaminess of the text (thanks to Mr. Robinson ;-)

        // We set both haminess and spaminess to 1 for the first multiplying
        $haminess  = 1;
        $spaminess = 1;

        // Consider all relevant ratings
        foreach ($relevant as $value) {
            $haminess  *= (1.0 - $value);
            $spaminess *= $value;
        }

        // If no token was good for calculation, we really don't know how to rate this text, so
        // we can return 0.5 without further calculations.
        if ($haminess == 1 && $spaminess == 1) {
            return 0.5;
        }

        // Calculate the combined rating

        // Get the number of relevant ratings
        $n = count($relevant);

        // The actual haminess and spaminess
        $haminess  = 1 - pow($haminess,  (1 / $n));
        $spaminess = 1 - pow($spaminess, (1 / $n));

        // Calculate the combined indicator
        $probability = ($haminess - $spaminess) / ($haminess + $spaminess);

        // We want a value between 0 and 1, not between -1 and +1, so ...
        $probability = (1 + $probability) / 2;

        // Alea iacta est
        return $probability;
    }

    /**
     * Calculate the spaminess of a single token also considering "degenerated" versions
     *
     * @access private
     * @param string The word to rate
     * @param array The "internals" array
     * @return float The word's rating
     */
    private function get_probability(string $word, array $internals)
    {
        // Let's see what we have!
        if (isset($this->token_data['tokens'][$word])) {
            // The token is in the database, so we can use it's data as-is and calculate the
            // spaminess of this token directly
            return $this->calculate_probability($this->token_data['tokens'][$word], $internals);
        }

        // The token was not found, so do we at least have similar words?
        if (isset($this->token_data['degenerates'][$word])) {
            // We found similar words, so calculate the spaminess for each one and choose the most
            // important one for the further calculation

            // The default rating is 0.5 simply saying nothing
            $rating = 0.5;

            foreach ($this->token_data['degenerates'][$word] as $degenerate => $count) {
                // Calculate the rating of the current degenerated token
                $rating_tmp = $this->calculate_probability($count, $internals);

                // Is it more important than the rating of another degenerated version?
                if(abs(0.5 - $rating_tmp) > abs(0.5 - $rating)) {
                    $rating = $rating_tmp;
                }
            }

            return $rating;
        } else {
            // The token is really unknown, so choose the default rating for completely unknown
            // tokens. This strips down to the robX parameter so we can cheap out the freaky math
            // ;-)
            return $this->config['rob_x'];
        }
    }

    /**
     * Do the actual spaminess calculation of a single token
     *
     * @access private
     * @param array The token's data [ \b8\b8::KEY_COUNT_HAM  => int,
                                       \b8\b8::KEY_COUNT_SPAM => int ]
     * @param array The "internals" array
     * @return float The rating
     */
    private function calculate_probability(array $data, array $internals)
    {
        // Calculate the basic probability as proposed by Mr. Graham

        // But: consider the number of ham and spam texts saved instead of the number of entries
        // where the token appeared to calculate a relative spaminess because we count tokens
        // appearing multiple times not just once but as often as they appear in the learned texts.

        $rel_ham = $data[\b8\b8::KEY_COUNT_HAM];
        $rel_spam = $data[\b8\b8::KEY_COUNT_SPAM];

        if ($internals[\b8\b8::KEY_TEXTS_HAM] > 0) {
            $rel_ham = $data[\b8\b8::KEY_COUNT_HAM] / $internals[\b8\b8::KEY_TEXTS_HAM];
        }

        if ($internals[\b8\b8::KEY_TEXTS_SPAM] > 0) {
            $rel_spam = $data[\b8\b8::KEY_COUNT_SPAM] / $internals[\b8\b8::KEY_TEXTS_SPAM];
        }

        $rating = $rel_spam / ($rel_ham + $rel_spam);

        // Calculate the better probability proposed by Mr. Robinson
        $all = $data[\b8\b8::KEY_COUNT_HAM] + $data[\b8\b8::KEY_COUNT_SPAM];
        return (($this->config['rob_s'] * $this->config['rob_x']) + ($all * $rating))
               / ($this->config['rob_s'] + $all);
    }

    /**
     * Check the validity of the category of a request
     *
     * @access private
     * @param string The category
     * @return void
     */
    private function check_category(string $category)
    {
        return $category === \b8\b8::HAM || $category === \b8\b8::SPAM;
    }

    /**
     * Learn a reference text
     *
     * @access public
     * @param string The text to learn
     * @param string Either b8::SPAM or b8::HAM
     * @return mixed void or an error code
     */
    public function learn(string $text = null, string $category = null)
    {
        // Let's first see if the user called the function correctly
        if ($text === null) {
            return \b8\b8::TRAINER_TEXT_MISSING;
        }
        if ($category === null) {
            return \b8\b8::TRAINER_CATEGORY_MISSING;
        }

        return $this->process_text($text, $category, \b8\b8::LEARN);
    }

    /**
     * Unlearn a reference text
     *
     * @access public
     * @param string The text to unlearn
     * @param string Either b8::SPAM or b8::HAM
     * @return mixed void or an error code
     */
    public function unlearn(string $text = null, string $category = null)
    {
        // Let's first see if the user called the function correctly
        if ($text === null) {
            return \b8\b8::TRAINER_TEXT_MISSING;
        }
        if ($category === null) {
            return \b8\b8::TRAINER_CATEGORY_MISSING;
        }

        return $this->process_text($text, $category, \b8\b8::UNLEARN);
    }

    /**
     * Does the actual interaction with the storage backend for learning or unlearning texts
     *
     * @access private
     * @param string The text to process
     * @param string Either b8::SPAM or b8::HAM
     * @param string Either b8::LEARN or b8::UNLEARN
     * @return mixed void or an error code
     */
    private function process_text(string $text, string $category, string $action)
    {
        // Look if the request is okay
        if (! $this->check_category($category)) {
            return \b8\b8::TRAINER_CATEGORY_FAIL;
        }

        // Get all tokens from $text
        $tokens = $this->lexer->get_tokens($text);

        // Check if the lexer failed (if so, $tokens will be a lexer error code, if not, $tokens
        //  will be an array)
        if (! is_array($tokens)) {
            return $tokens;
        }

        // Pass the tokens and what to do with it to the storage backend
        return $this->storage->process_text($tokens, $category, $action);
    }

}
