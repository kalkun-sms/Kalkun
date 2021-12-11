<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @copyright 2021 Fab Stz
 * @author Fab Stz <fabstz-it@yahoo.fr>
 * @license MIT
 * @link https://github.com/kalkun-sms/Kalkun/
 */

/**
 * Language Class
  */
class MY_Lang extends MX_Lang {

	// Default to 'en'
	public $locale = 'en';

	public static $idiom_to_locale = [
		'portuguese-brazilian' => 'pt_BR',
		'czech' => 'cs',
		'danish' => 'da',
		'dutch' => 'nl',
		'english' => 'en',
		'finnish' => 'fi',
		'french' => 'fr',
		'german' => 'de',
		'hungarian' => 'hu',
		'indonesian' => 'in',
		'italian' => 'it',
		'polish' => 'pl',
		'portuguese' => 'pt',
		'russian' => 'ru',
		'slovak' => 'sk',
		'spanish' => 'es',
		'turkish' => 'tr',
	];

	private $locale_matching_browser = NULL;

	/**
	 * Class constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
		if ( ! extension_loaded('intl'))
		{
			log_message('error', 'please install/enable the intl extension of PHP');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Load a language file
	 *
	 * @param	mixed	$langfile	Language file name
	 * @param	string	$idiom		Language name (english, etc.)
	 * @param	bool	$return		Whether to return the loaded array of translations
	 * @param 	bool	$add_suffix	Whether to add suffix to $langfile
	 * @param 	string	$alt_path	Alternative path to look for the language file
	 * @param 	string	$_module	see MX_Loader
	 *
	 * @return	void|string[]	Array containing translations, if $return is set to TRUE
	 */
	public function load($langfile, $idiom = '', $return = FALSE, $add_suffix = TRUE, $alt_path = '', $_module = '')
	{
		if ($idiom !== '')
		{
			$this->idiom = $idiom;
		}

		$requested_idiom = $this->idiom;
		// Check if language file exists
		if ( ! file_exists(APPPATH.'language/'.$this->idiom.'/'.$langfile.'_lang.php'))
		{
			$requested_idiom = 'english';
		}

		parent::load($langfile, $requested_idiom, $return, $add_suffix, $alt_path, $_module);
		if ( ! empty($idiom))
		{
			$this->locale = MY_LANG::$idiom_to_locale[$idiom];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Language line
	 *
	 * Fetches a single line of text from the language array
	 *
	 * @param	string	$line		Language line key
	 * @param	bool	$log_errors	Whether to log an error message if the line is not found
	 * @return	string	Translation
	 */

	public function line($line, $log_errors = TRUE)
	{
		return $this->__call('line', func_get_args());
	}

	/**
	 * Language line
	 *
	 * Fetches a single line of text from the language array
	 *
	 * @param	string	$line		Language line key
	 * @param	string	$context	context of the line (used to search in the nested array of the line)
	 * @param	array	$msg_params	the arguments to pass to MessageFormatter::formatMessage
	 * @return	string	Translation
	 */
	private function line_kalkun($line, $context = NULL, ...$msg_params)
	{
		if ($context === NULL)
		{
			if (isset($this->language[$line]))
			{
				if (extension_loaded('intl'))
				{
					$value = MessageFormatter::formatMessage(
						$this->locale,
						$this->language[$line],
						$msg_params
					);
				}
				else
				{
					$value = parent::line($line);
				}
			}
			else
			{
				$value = FALSE;
			}
		}
		else
		{
			if (is_string($context))
			{
				if (isset($this->language[$line]) && isset($this->language[$line][$context]))
				{
					if (extension_loaded('intl'))
					{
						$value = MessageFormatter::formatMessage(
							$this->locale,
							$this->language[$line][$context],
							$msg_params
						);
					}
					else
					{
						$value = parent::line($line);
					}
				}
				else
				{
					$value = FALSE;
				}
			}
			else
			{
				log_message('error', 'context for message must be either NULL or a string');
			}
		}


		//$value = isset($this->language[$line]) ? $this->language[$line] : FALSE;

		// Because killer robots like unicorns!
		if ($value === FALSE)
		{
			$value = '🌐 '.$line;
			log_message('error', 'Could not find the language line "'.$line.'"');
		}

		return $value;
	}

	// https://stackoverflow.com/a/2147799/15401262
	public function __call($method, $arguments)
	{
		if ($method === 'line')
		{
			if (count($arguments) === 0)
			{
				return call_user_func_array('parent::line', $arguments);
			}
			if (count($arguments) === 1)
			{
				return call_user_func_array(array($this, 'line_kalkun'), $arguments);
			}
			if (count($arguments) === 2)
			{
				if (is_bool($arguments[1]))
				{
					return call_user_func_array('parent::line', $arguments);
				}
				else
				{
					return call_user_func_array(array($this, 'line_kalkun'), $arguments);
				}
			}
			else
			{
				if (count($arguments) >= 3)
				{
					return call_user_func_array(array($this, 'line_kalkun'), $arguments);
				}
			}
		}
	}

	public static function locale_to_idiom ($locale)
	{
		$idiom_to_locale_lc = array_map('strtolower', MY_Lang::$idiom_to_locale);
		$idiom = array_search(strtolower($locale), $idiom_to_locale_lc);
		if ($idiom === FALSE)
		{
			$idiom = 'english';
		}
		return $idiom;
	}
	static function supported_locales()
	{
		return array_values(MY_Lang::$idiom_to_locale);
	}

	// https://www.codingwithjesse.com/blog/use-accept-language-header/
	static function browser_accept_language()
	{
		$langs = array();

		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			// break up string into pieces (languages and q factors)
			preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

			if (count($lang_parse[1]))
			{
				// create a list like "en" => 0.8
				$langs = array_combine($lang_parse[1], $lang_parse[4]);

				// set default to 1 for any without q factor
				foreach ($langs as $lang => $val)
				{
					if ($val === '')
					{
						$langs[$lang] = 1;
					}
				}

				// sort list based on value
				arsort($langs, SORT_NUMERIC);
			}
		}

		return $langs;
	}

	function locale_matching_browser()
	{
		if (isset($this->locale_matching_browser))
		{
			return $this->locale_matching_browser;
		}

		$locale = NULL;
		$supported_locales = MY_Lang::supported_locales();
		//$supported_locales_short = array_map('MY_Lang::locale_language', $supported_locales);
		//$supported_locales_all = array_merge($supported_locales, $supported_locales_short);

		// look through sorted list and use first one that matches our languages
		foreach (MY_Lang::browser_accept_language() as $lang => $val)
		{
			if (extension_loaded('intl'))
			{
				$locale = locale_lookup($supported_locales, $lang, TRUE, NULL);
			}
			else
			{
				if (in_array($lang, $supported_locales))
				{
					$locale = $lang;
				}
			}
			if ( ! empty($locale))
			{
				$this->locale_matching_browser = $locale;
				return $this->locale_matching_browser;
			}
		}

		$this->locale_matching_browser = 'en';
		return $this->locale_matching_browser;
	}

	static function locale_language($locale)
	{
		if (extension_loaded('intl'))
		{
			return Locale::parseLocale($locale)['language'];
		}
		else
		{
			$locale = explode('-', $locale)[0];
			return explode('_', $locale)[0];
		}
	}

	function get_idiom()
	{
		$locale = $this->locale_matching_browser();
		$this->idiom = MY_Lang::locale_to_idiom($locale);
		return $this->idiom;
	}

	public function kalkun_supported_languages()
	{
		$supported_languages = [];
		foreach (MY_Lang::$idiom_to_locale as $key => $value)
		{
			$supported_languages[$key] = Locale::getDisplayName($value, $value);
		}
		natcasesort($supported_languages);
		return $supported_languages;
	}
}
