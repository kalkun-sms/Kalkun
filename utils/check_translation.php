#!/usr/bin/php
<?php
if (php_sapi_name() !== 'cli') exit;

/**
 * This is part of kalkun - a web based SMS manager
 * Copyright: 2021 Fab Stz <fabstz-it@yahoo.fr>
 * License: GPL-2.0-or-later
 *
 * This script will recursively check the PHP files in a directory for
 * translation inconsistencies
 *   - labels used by tr() but not in the translation file
 *   - labels that are not consistent in the english file
 *   - labels that are in the translation file, but never used.
 *
 * Usage:
 *   utils/check_translation.php [all|application|application/plugins/<plugin_name>]
 *   php -f utils/check_translation.php [all|application|application/plugins/<plugin_name>]
 *
 *  /!\ This must be run from the root dir of the project
 *
 *   directory:
 *       The directory containing the files to check.
 *       - all: check core application + plugins
 *       - application: the core application without the plugins
 *       - application/plugins/<plugin_name>: the given plugin only
 *
 */

// The script needs PhpParser to parse kalkun's PHP code
include_once 'vendor/autoload.php';

use PhpParser\Error;
use PhpParser\ParserFactory;
use PhpParser\Node;
use PhpParser\NodeFinder;

/**
 * Search recusively for files in a base directory matching a glob pattern.
 * The `GLOB_NOCHECK` flag has no effect.
 *
 * @param  string $base Directory to search
 * @param  string $pattern Glob pattern to match files
 * @param  int $flags Glob flags from https://www.php.net/manual/function.glob.php
 * @return string[] Array of files matching the pattern
 *
 * License: DWTFYW
 * https://gist.github.com/UziTech/3b65b2543cee57cd6d2ecfcccf846f20
 */
function glob_recursive($base, $pattern, $flags = 0)
{
	$flags = $flags & ~GLOB_NOCHECK;

	if (substr($base, -1) !== DIRECTORY_SEPARATOR)
	{
		$base .= DIRECTORY_SEPARATOR;
	}

	$files = glob($base.$pattern, $flags);
	if ( ! is_array($files))
	{
		$files = [];
	}

	$dirs = glob($base.'*', GLOB_ONLYDIR | GLOB_NOSORT | GLOB_MARK);
	if ( ! is_array($dirs))
	{
		return $files;
	}

	foreach ($dirs as $dir)
	{
		$dirFiles = glob_recursive($dir, $pattern, $flags);
		$files = array_merge($files, $dirFiles);
	}

	return $files;
}

/**
* array_merge_recursive does indeed merge arrays, but it converts values with duplicate
* keys to arrays rather than overwriting the value in the first array with the duplicate
* value in the second array, as array_merge does. I.e., with array_merge_recursive,
* this happens (documented behavior):
*
* array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
*     => array('key' => array('org value', 'new value'));
*
* array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
* Matching keys' values in the second array overwrite those in the first array, as is the
* case with array_merge, i.e.:
*
* array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
*     => array('key' => array('new value'));
*
* Parameters are passed by reference, though only for performance reasons. They're not
* altered by this function.
*
* @param array $array1
* @param array $array2
* @return array
* @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
* @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
*/
function array_merge_recursive_distinct (array &$array1, array &$array2)
{
	$merged = $array1;

	foreach ($array2 as $key => &$value)
	{
		if (is_array ($value) && isset ($merged[$key]) && is_array ($merged[$key]))
		{
			$merged[$key] = array_merge_recursive_distinct ($merged[$key], $value);
		}
		else
		{
			$merged[$key] = $value;
		}
	}

	return $merged;
}

/**
 * Returns if $str contains $search
 *
 * @param string $str
 * @param sting $search
 * @return boolean
 */
function contains($str, $search)
{
	if (strpos($str, $search) !== FALSE)
	{
		return TRUE;
	}
	return FALSE;
}


/**
 *
 * @param string $basedir
 * @param boolean $with_plugins
 * @return array
 */
function get_all_tr_labels($basedir, $with_plugins)
{
	$php_files = glob_recursive($basedir, '*.php');
	//var_dump($php_files);

	$all_labels = [];
	foreach ($php_files as $filename)
	{
		// If we are parsing the core of kalkun, don't load the php files of the plugins.
		// Parsing of the plugins should be made separately
		if ( ! contains($basedir, '/plugins')
		  && contains($filename, '/plugins')
		  && ! contains($filename, '/plugins/Plugin_')
		  ) {
			if ( ! $with_plugins)
			{
				continue;
			}
		}

		//var_dump($filename);
		$code = file_get_contents($filename);

		$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

		try
		{
			$ast = $parser->parse($code);
		}
		catch (Error $error)
		{
			echo "Parse error: {$error->getMessage()}\n";
			return;
		}

		$additonal_labels = get_tr_labels($ast);
		$all_labels = array_merge_recursive_distinct($all_labels, $additonal_labels);
	}
	//var_dump($all_labels);
	//$j = json_encode($all_labels, JSON_PRETTY_PRINT);
	//echo $j;
	return $all_labels;
}

/**
 * Extract tr labels with the help of PHPParser
 *
 * @param type $ast
 * @return array
 */
function get_tr_labels($ast)
{
	$nodeFinder = new NodeFinder;
	$expr = $nodeFinder->findInstanceOf($ast, Node\Expr\FuncCall::class);

	$labels = [];
	$labels2 = [];
	foreach ($expr as $f)
	{
		$fn_name = $f->name->toCodeString();
		//var_dump($fn_name);

		if ($fn_name === 'tr' || $fn_name === 'tr_addcslashes' || $fn_name === 'tr_js' || $fn_name === 'tr_raw')
		{
			if ($fn_name === 'tr' || $fn_name === 'tr_js' || $fn_name === 'tr_raw')
			{
				$tr_args = $f->args;
			}
			else
			{
				$tr_args = array_slice($f->args, 1);
			}

			$label = $tr_args[0]->value->value;
			$item['label'] = $label;
			//var_dump($label);
			if (sizeof($tr_args) > 1)
			{
				//var_dump($tr_args[1]->value);
				if ($tr_args[1]->value instanceof PhpParser\Node\Expr\ConstFetch)
				{
					$context = $tr_args[1]->value->name->toCodeString();
					if ($context === 'NULL')
					{
						$labels2[$label] = '';
					}
					else
					{
						$item['context'] = $context;
						$labels2[$label][$context] = '';
					}
				}
				else
				{
					$context = $tr_args[1]->value->value;
					$item['context'] = $context;
					$labels2[$label][$context] = '';
				}
			}
			else
			{
				$labels2[$label] = '';
			}
			array_push($labels, $item);
		}
	}
	//var_dump($labels2);
	return $labels2;
}


/**
 * Check if key & value of translations are the same.
 *
 * @param array $lang_array
 * @return array
 */
function check_discordant_label($lang_array)
{
	//var_dump($lang_array);

	$discordant = [];
	foreach ($lang_array as $file => $lang)
	{
		$count_discordant_label = 0;
		foreach ($lang as $key => $value)
		{
			if (is_array($value))
			{
				foreach ($value as $k => $v)
				{
					if ($key !== $v)
					{
						$discordant[$file][$key] = $v;
					}
					else
					{
						//echo "OK: $v [in context: $k]\n";
					}
				}
			}
			else
			{
				if ($key !== $value)
				{
					$discordant[$file][$key] = $value;
				}
				else
				{
					//echo "OK: $value\n";
				}
			}
		}
	}
	return $discordant;
}

/**
 * Check unused translations (only in english)
 *
 * @param type $available
 * @param type $requested
 * @return type
 *
 */
function check_unused_labels($available, $requested)
{
	$unused = [];

	foreach ($available as $file => $lang)
	{
		foreach ($lang as $key => $value)
		{
			if (is_array($value))
			{
				if (array_key_exists($key, $requested) && is_array($requested[$key]))
				{
					if ( ! array_key_exists(key($value), $requested[$key]))
					{
						$unused[$file][$key][key($value)] = $value[key($value)] ;
					}
				}
				else
				{
					$unused[$file][$key] = $value ;
				}
			}
			else
			{
				if ( ! array_key_exists($key, $requested) || is_array($requested[$key]))
				{
					$unused[$file][$key] = $value ;
				}
			}
		}
	}
	return $unused;
}

/**
 *
 * display_cmd_to_remove_labels
 *
 * @param array $unused
 * @param string $basedir
 * @return type
 */
function display_cmd_to_remove_labels($unused, $basedir)
{
	if (sizeof($unused) === 0)
	{
		return;
	}
	echo "\nRun these commands to remove them:";
	foreach ($unused as $file => $lang)
	{
		$basename = basename($file);
		foreach ($lang as $key => $value)
		{
			$escaped_key = $key;
			$escaped_key = str_replace("'", "\\\'\''", $escaped_key);
			$escaped_key = str_replace("/", "\\/", $escaped_key);
			echo "\n";
			if (is_array($value))
			{
				echo "for lang in $(find ${basedir}/language -mindepth 1 -type d | sort); do sed -i -e '/\$lang\['\''${escaped_key}'\''\]\['\''".key($value)."'\''\]/ d' \$lang/${basename}; done";
			}
			else
			{
				echo "for lang in $(find ${basedir}/language -mindepth 1 -type d | sort); do sed -i -e '/\$lang\['\''${escaped_key}'\''\]/ d' \$lang/${basename}; done";
			}
		}
	}
	echo "\n";
}

/**
 * Check untranslated string
 * Note: this is also displayed in the GUI by showing a globe icon next to the label
 *
 * @param array $lang_array
 * @param array $requested
 * @return type
 *
 */
function check_missing_labels($lang_array, $requested)
{
	$missings = [];

	$available = [];
	foreach ($lang_array as $item)
	{
		$available = array_merge($available, $item);
	}

	foreach ($requested as $key => $value)
	{
		if (is_array($value))
		{
			if (array_key_exists($key, $available) && is_array($available[$key]))
			{
				if ( ! array_key_exists(key($value), $available[$key]))
				{
					//Missing
					$missings[$key][key($value)] = $value[key($value)] ;
				}
			}
			else
			{
				//Missing
				$missings[$key] = $value ;
			}
		}
		else
		{
			if (array_key_exists($key, $available))
			{
				//echo "OK: $key\n";
			}
			else
			{
				$missings[$key] = $value ;
			}
		}
	}
	return $missings;
}

/**
 * display_cmd_to_add_labels
 *
 * @param array $missings
 * @param string $basedir
 * @return type
 */
function display_cmd_to_add_labels($missings, $basedir)
{
	if (sizeof($missings) === 0)
	{
		return;
	}
	echo "\nRun these commands to add them:\n\n";
	echo "export KALKUN_LANG_OUTPUT_FILE=\$(basename \$(find ${basedir}/language -name '*_lang.php' | rev | cut -d '/' -f 1 | rev | sort -u | grep -v date_lang | head -1))\n";
	foreach ($missings as $key => $value)
	{
		$escaped_key = $key;
		$escaped_key = str_replace("'", "\\'\''", $escaped_key);
		if (is_array($value))
		{
			echo "for lang in $(find ${basedir}/language -mindepth 1 -type d | sort); do echo '\$lang['\''${escaped_key}'\'']['\''".key($value)."'\''] = '\''${escaped_key}'\'';' >> \$lang/\$KALKUN_LANG_OUTPUT_FILE; done";
		}
		else
		{
			echo "for lang in $(find ${basedir}/language -mindepth 1 -type d | sort); do echo '\$lang['\''${escaped_key}'\''] = '\''${escaped_key}'\'';' >> \$lang/\$KALKUN_LANG_OUTPUT_FILE; done";
		}
		echo "\n";
	}
	echo "\n";
}

/**
 * Perform the translation check on "$basedir"
 * Return the total count of checks in error
 *
 * @param string $basedir
 * @return int
 *
 */
function perfom_check($basedir)
{
	$error_count = 0;

	// Load the $lang array that is in the _lang.php file
	$lang_array = [];

	defined('BASEPATH') || define('BASEPATH', '');
	foreach (glob("${basedir}/language/english/*_lang.php") as $filename)
	{
		include $filename;
		$lang_array[$filename] = $lang;
		unset($lang);
	}

	echo "Parsing PHP files in: ${basedir}\n";
	echo str_repeat('¯', 22 + strlen($basedir))."\n";

	echo "Checking unused labels...  \n";
	$unused_label = check_unused_labels($lang_array, get_all_tr_labels($basedir, TRUE));

	echo "Checking discordant labels in the english file...\n";
	$discordant_label = check_discordant_label($lang_array);

	echo "Checking missing labels...\n";
	// Load translations we already have ($lang array)
	$additional_lang_files = [
		'vendor/codeigniter/framework/system/language/english/date_lang.php',
		'application/language/english/kalkun_lang.php'
	];
	foreach ($additional_lang_files as $file)
	{
		include $file;
		$lang_array[$file] = $lang;
	}
	$missing_labels_in_english = check_missing_labels($lang_array, get_all_tr_labels($basedir, FALSE));
	//var_dump($lang);

	echo "\n";
	echo "Results:\n";

	$total_unused_labels = 0;
	foreach ($unused_label as $val)
	{
		$total_unused_labels += sizeof($val);
	}

	if ($total_unused_labels !== 0)
	{
		echo "- ${total_unused_labels} key(s) present in translation file but not used anywhere.\n";
		echo json_encode($unused_label, JSON_PRETTY_PRINT);
		echo "\n";
		display_cmd_to_remove_labels($unused_label, $basedir);
		echo "\n";
		$error_count++;
	}
	else
	{
		echo "- No unused labels found.\n";
	}

	if (sizeof($discordant_label) !== 0)
	{
		echo '- '.sizeof($discordant_label)." key(s) have a discordant label. Please fix by setting same value as key.\n";
		echo json_encode($discordant_label, JSON_PRETTY_PRINT);
		echo "\n\n";
		$error_count++;
	}
	else
	{
		echo "- No discordant labels found.\n";
	}

	if (sizeof($missing_labels_in_english) !== 0)
	{
		echo '- '.sizeof($missing_labels_in_english)." label(s) have no translation in an english *_lang.php file.\n";
		echo json_encode($missing_labels_in_english, JSON_PRETTY_PRINT);
		echo "\n";
		display_cmd_to_add_labels($missing_labels_in_english, $basedir);
		echo "\n";
		$error_count++;
	}
	else
	{
		echo "- No missing labels found.\n";
	}

	echo "\n";
	return $error_count;
}


/**
 * Main call
 *
 */

$total_error_count = 0;

if (sizeof($argv) > 1)
{
	if ($argv[1] === 'all')
	{
		$total_error_count += perfom_check('application');
		$plugin_dirs = glob('application/plugins/*', GLOB_ONLYDIR | GLOB_MARK);
		// Exclude some plugins from the check.
		$plugin_dirs = array_filter($plugin_dirs, function($v) {
			return false === strpos($v, 'application/plugins/rest_api/');
		});
		foreach ($plugin_dirs as $dir)
		{
			$total_error_count += perfom_check($dir);
		}
	}
	else
	{
		$total_error_count += perfom_check($argv[1]);
	}
}
else
{
	$total_error_count += perfom_check('application');
}

// We return status code 1 only is labels are discordant. Unused labels is only informational.
if ($total_error_count !== 0)
{
	echo "Total checks in error: ${total_error_count}\n";
}
exit ($total_error_count);
