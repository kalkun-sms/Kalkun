<?php

require_once 'stemmer.php';

/**
 * PHP version of Ruby Bayes classifier library
 * @see http://github.com/xaviershay/classifier
 */
class Bayes
{
	private $_categories;
	private $_totalWords;

	/**
	 * The class can be created with one or more categories, each of which will be
	 * initialized and given a training method. E.g.,
	 *	  $b = new Bayes('Interesting', 'Uninteresting', 'Spam')
	 */
	public function __construct()
	{
		$categories = func_get_args();

		$this->_totalWords = 0;

		$this->_categories = array();
		foreach ($categories as $category)
		{
			$this->_categories[$category] = array();
		}
	}

	/**
	 * Provides a general training method for all categories specified in Bayes#new
	 * For example:
	 *	 $b = new Bayes('this', 'that', 'the_other')
	 *	 $b->train('this', 'This text')
	 *	 $b->train('that', 'That text')
	 *	 $b->train('the_other', 'The other text')
	 */
	public function train($category, $text)
	{
		foreach ($this->_wordArray($text) as $word => $count)
		{
			if (!isset($this->_categories[$category][$word]))
				$this->_categories[$category][$word] = 0;

			$this->_categories[$category][$word] += $count;

			$this->_totalWords += $count;
		}
	}

	/**
	 * Returns the scores in each category the provided +text+. E.g.,
	 *	$b->classifications("I hate bad words and you")
	 *	=>  {"Uninteresting" => -12.6997928013932, "Interesting" => -18.4206807439524}
	 * The largest of these scores (the one closest to 0) is the one picked out by classify()
	 */
	public function classifications($text)
	{
		$score = array();

		foreach ($this->_categories as $category => $categoryWords)
		{
			$score[$category] = 0.0;
			$total = array_sum(array_values($categoryWords));

			foreach ($this->_wordArray($text) as $word => $count)
			{
				$s = isset($categoryWords[$word]) ? $categoryWords[$word] : 0.1;
				$score[$category] += log($s / $total);
			}
		}

		return $score;
	}

	/**
	 * Returns the classification of the provided +text+, which is one of the
	 * categories given in the initializer. E.g.,
	 *	$b->classify("I hate bad words and you")
	 *	=>  'Uninteresting'
	 */
	public function classify($text)
	{
		$a = $this->classifications($text);
		arsort($a);
		return array_shift(array_keys($a));
	}

	// ----

	/**
	 * Return an array of strings => ints. Each word in the string is stemmed,
	 * and indexes to its frequency in the document.
	 */
	private function _wordArray($word)
	{
		return $this->_wordArrayForWords(
			array_merge(
				preg_split('/\s+/', preg_replace('/[^\w\s]/','', $word)),
				preg_split('/\s+/', preg_replace('/[\w]/',' ', $word))));
	}

	private function _wordArrayForWords($words)
	{
		$d = array();

		foreach ($words as $word)
		{
			if (preg_match('/[\w]+/',$word)) $word = strtolower($word);
			$key = PorterStemmer::Stem($word);

			if (preg_match('/[^\w]/',$word)
				|| !in_array($word, self::$CORPUS_SKIP_WORDS)
				&& strlen($word) > 2)
			{
				if (!isset($d[$key]))
					$d[$key] = 0;

				$d[$key] += 1;
			}
		}

		return $d;
	}

	private static $CORPUS_SKIP_WORDS = array(
		"a",
		"again",
		"all",
		"along",
		"are",
		"also",
		"an",
		"and",
		"as",
		"at",
		"but",
		"by",
		"came",
		"can",
		"cant",
		"couldnt",
		"did",
		"didn",
		"didnt",
		"do",
		"doesnt",
		"dont",
		"ever",
		"first",
		"from",
		"have",
		"her",
		"here",
		"him",
		"how",
		"i",
		"if",
		"in",
		"into",
		"is",
		"isnt",
		"it",
		"itll",
		"just",
		"last",
		"least",
		"like",
		"most",
		"my",
		"new",
		"no",
		"not",
		"now",
		"of",
		"on",
		"or",
		"should",
		"sinc",
		"so",
		"some",
		"th",
		"than",
		"this",
		"that",
		"the",
		"their",
		"then",
		"those",
		"to",
		"told",
		"too",
		"true",
		"try",
		"until",
		"url",
		"us",
		"were",
		"when",
		"whether",
		"while",
		"with",
		"within",
		"yes",
		"you",
		"youll",
		);
}

