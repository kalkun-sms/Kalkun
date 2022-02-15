<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kalkun
 * An open source web based SMS Manager
 *
 * @package     Kalkun
 * @author      Kalkun Dev Team
 * @license     <https://spdx.org/licenses/GPL-3.0-or-later.html> GPL-3.0-or-later
 * @link        https://github.com/kalkun-sms/Kalkun/
 */

/**
*	INDIA NCPR(DND) Registry Check
*	In order to avoid sending sms to NCPR registered phone numbers
**/
function DNDcheck($mobileno)
{
	$mobileno = substr($mobileno, -10, 10);
	$url = 'http://www.nccptrai.gov.in/nccpregistry/saveSearchSub.misc';
	$postString = 'phoneno=' . $mobileno;
	$request = curl_init($url);
	curl_setopt($request, CURLOPT_HEADER, 0);
	//curl_setopt($request , CURLOPT_PROXY , '10.3.100.211:8080' );
	curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($request, CURLOPT_POST, 1);
	curl_setopt($request, CURLOPT_POSTFIELDS, $postString);
	curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);
	$response = curl_exec($request);
	curl_close ($request);

	return (is_int(strpos(strtolower(strip_tags($response)), 'number is not')) ? FALSE : TRUE);
}

function filter_data($data)
{
	if ( ! isset($data))
	{
		return '<i>Unknown</i>';
	}
	else
	{
		return $data;
	}
}

function nice_date($str, $option = NULL)
{
	$CI = &get_instance();
	$CI->load->helper('date');

	// convert the date to unix timestamp
	list($date, $time) = explode(' ', $str);
	list($year, $month, $day) = explode('-', $date);
	list($hour, $minute, $second) = explode(':', $time);

	$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
	$now = now();
	$blocks = array(
		array('name' => lang('kalkun_year'), 'amount' => 60 * 60 * 24 * 365),
		array('name' => lang('kalkun_month'), 'amount' => 60 * 60 * 24 * 31),
		array('name' => lang('kalkun_week'), 'amount' => 60 * 60 * 24 * 7),
		array('name' => lang('kalkun_day'), 'amount' => 60 * 60 * 24),
		array('name' => lang('kalkun_hour'), 'amount' => 60 * 60),
		array('name' => lang('kalkun_minute'), 'amount' => 60),
		array('name' => lang('kalkun_second'), 'amount' => 1)
	);

	$diff = abs($now - $timestamp);

	if ($option === 'smsd_check')
	{
		return $diff;
	}
	else
	{
		if ($diff < 60)
		{
			return lang('kalkun_nicedate_less1min_ago'); // "Less than a minute ago"
		}
		else
		{
			$levels = 1;
			$current_level = 1;
			$result = array();
			foreach ($blocks as $block)
			{
				if ($current_level > $levels)
				{
					break;
				}
				if ($diff / $block['amount'] >= 1)
				{
					$amount = floor($diff / $block['amount']);
					$plural = '';
					//if ($amount>1) {$plural='s';} else {$plural='';}
					$result[] = $amount.' '.$block['name'].$plural;
					$diff -= $amount * $block['amount'];
					$current_level += 1;
				}
			}
			$res = implode(' ', $result);

			if ($timestamp > $now)
			{
				$text = lang('kalkun_nicedate_remaining');
			}
			else
			{
				$text = lang('kalkun_nicedate_ago');
			}
			return str_replace('%nicedate%', $res, $text);
		}
	}
}

function get_modem_status($status, $tolerant)
{
	// convert the date to unix timestamp
	list($date, $time) = explode(' ', $status);
	list($year, $month, $day) = explode('-', $date);
	list($hour, $minute, $second) = explode(':', $time);

	$timestamp = mktime($hour, $minute + $tolerant, $second, $month, $day, $year);
	$now = time();

	//$diff = abs($now-$timestamp);
	if ($timestamp > $now)
	{
		return 'connect';
	}
	else
	{
		return 'disconnect';
	}
}

function message_preview($str, $n)
{
	if (strlen($str) <= $n)
	{
		return showtags($str);
	}
	else
	{
		return showtags(substr($str, 0, $n - 3)).'&#8230;';
	}
}

function showtags($msg)
{
	$msg = preg_replace('/</', '&lt;', $msg);
	$msg = preg_replace('/>/', '&gt;', $msg);
	return $msg;
}

function showmsg($msg)
{
	return nl2br(showtags($msg));
}

function compare_date_asc($a, $b)
{
	$date1 = strtotime($a['globaldate']);
	$date2 = strtotime($b['globaldate']);

	if ($date1 === $date2)
	{
		return 0;
	}
	return ($date1 < $date2) ? -1 : 1;
}

function compare_date_desc($a, $b)
{
	$date1 = strtotime($a['globaldate']);
	$date2 = strtotime($b['globaldate']);

	if ($date1 === $date2)
	{
		return 0;
	}
	return ($date1 > $date2) ? -1 : 1;
}

function check_delivery_report($report)
{
	if ($report === 'SendingError' OR $report === 'Error' OR $report === 'DeliveryFailed')
	{
		$status = lang('tni_msg_stat_fail');
	}
	elseif ($report === 'SendingOKNoReport')
	{
		$status = lang('tni_msg_stat_oknr');
	}
	elseif ($report === 'SendingOK')
	{
		$status = lang('tni_msg_stat_okwr');
	}
	elseif ($report === 'DeliveryOK')
	{
		$status = lang('tni_msg_stat_deliv');
	}
	elseif ($report === 'DeliveryPending')
	{
		$status = lang('tni_msg_stat_pend');
	}
	elseif ($report === 'DeliveryUnknown')
	{
		$status = lang('tni_msg_stat_unknown');
	}
	elseif ($report === 'Reserved')
	{
		$status = lang('tni_msg_stat_reserved');
	}

	return $status;
}

function simple_date($datetime)
{
	list($date, $time) = explode(' ', $datetime);
	list($year, $month, $day) = explode('-', $date);
	return $day.'/'.$month.'/'.$year.' '.$time;
}

function get_hour()
{
	for ($i = 0;$i < 24;$i++)
	{
		$hour = $i;
		if ($hour < 10)
		{
			$hour = '0'.$hour;
		}
		echo '<option value="'.$hour.'">'.$hour.'</option>';
	}
}

function get_minute()
{
	for ($i = 0;$i < 60;$i = $i + 5)
	{
		$min = $i;
		if ($min < 10)
		{
			$min = '0'.$min;
		}
		echo '<option value="'.$min.'">'.$min.'</option>';
	}
}

function is_ajax()
{
	if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

function get_database_property($driver)
{
	// valid and supported driver
	$valid_driver = array('postgre', 'mysql', 'mysqli', 'pdo');

	if ( ! in_array($driver, $valid_driver))
	{
		die ("Database driver you're using is not supported");
	}

	$postgre['name'] = 'postgre';
	$postgre['file'] = 'pgsql';
	$postgre['human'] = 'PostgreSQL';
	$postgre['escape_char'] = '"';
	$postgre['driver'] = 'pgsql';

	$mysql['name'] = 'mysql';
	$mysql['file'] = 'mysql';
	$mysql['human'] = 'MySQL';
	$mysql['escape_char'] = '`';
	$mysql['driver'] = 'mysql';

	$mysqli['name'] = 'mysqli';
	$mysqli['file'] = 'mysql';
	$mysqli['human'] = 'MySQLi';
	$mysqli['escape_char'] = '`';
	$mysqli['driver'] = 'mysqli';

	$pdo['name'] = 'sqlite';
	$pdo['file'] = 'sqlite';
	$pdo['human'] = 'SQLite3 (Using PDO)';
	$pdo['escape_char'] = '';
	$pdo['driver'] = 'pdo_sqlite';

	return ${$driver};
}

/**
 * Execute SQL
 *
 * Run SQL command from file, line by line
 */
function execute_sql($sqlfile)
{
	$CI = &get_instance();
	$CI->load->model('Kalkun_model');

	$error = 0;
	if ($lines = @file($sqlfile, FILE_SKIP_EMPTY_LINES))
	{
		$buff = '';
		foreach ($lines as $i => $line)
		{
			if (preg_match('/^--/', $line))
			{
				continue;
			}
			$buff .= $line . "\n";
			if (preg_match('/;$/', trim($line)))
			{
				// if contains TRIGGER
				if (preg_match('/CREATE TRIGGER$/', trim($line)))
				{
					$buff .= ' END;';
				}
				$query = $CI->Kalkun_model->db->query($buff);
				if ( ! $query)
				{
					$error++;
				}
				$buff = '';
			}
		}
	}
	return $error;
}

/**
 * Database boolean to PHP boolean
 *
 * Convert data that is stored as boolean in the database to
 * a php bool type (true or false)
 */
function db_boolean_to_php_bool($dbdriver, $db_bool)
{
	switch ($dbdriver) {
		case 'postgre':
			if ($db_bool === 't')
			{
				return TRUE;
			}
			//if ($db_bool === 'f') {
			return FALSE;
			//}
		case 'mysql':
		case 'mysqli':
		case 'pdo':
		default:
			return boolval($db_bool);
	}
}

/**
 * Equivalent to "$var == NULL"
 *
 * This function returns the same result as "$var == NULL" but by using
 * strict comparison operator
  */
function is_null_loose($input)
{
	// doing $input == NULL is like doing 'empty($input)' except that
	// empty() returns true if the value is "0".
	// So in that case, return FALSE so that we can mimic '$input == NULL'
	if (isset($input) && is_string($input) && $input === '0')
	{
		return FALSE;
	}
	return empty($input);
}

/**
 * Convert a phone number as input by the user to E164 format
 * using the region of the user.
 * Done with libphonenumber
 *
 * @param string $phone
 * @return string
 */
function phone_format_e164($phone)
{
	$CI = &get_instance();
	// reformat phone number to E164
	$phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
	$region = $CI->Kalkun_model->get_setting()->row('country_code');
	$phoneNumberObject = $phoneNumberUtil->parse($phone, $region);
	$phone_number = $phoneNumberUtil->format($phoneNumberObject, \libphonenumber\PhoneNumberFormat::E164);
	return $phone_number;
}

/**
 * Convert a phone number as input to human readable format
 * NATIONAL if same region as user, otherwise INTERNATIONAL
 * Done with libphonenumber
 *
 * @param string $phone
 * @return string
 */
function phone_format_human($phone)
{
	$CI = &get_instance();

	try
	{
		$region = $CI->Kalkun_model->get_setting()->row('country_code');

		$phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
		$phoneNumberObject = $phoneNumberUtil->parse($phone, $region);

		$phone_region = $phoneNumberUtil->getRegionCodeForNumber($phoneNumberObject);

		if ($region === $phone_region)
		{
			$phone_number = $phoneNumberUtil->format($phoneNumberObject, \libphonenumber\PhoneNumberFormat::NATIONAL);
		}
		else
		{
			$phone_number = $phoneNumberUtil->format($phoneNumberObject, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
		}
		return $phone_number;
	}
	catch (Exception $e)
	{
		return $phone;
	}
}

/**
 *
 * @author Sergey Shuchkin
 * @link https://stackoverflow.com/a/12196609/15401262
 * @license CC-BY-SA-3.0.html
 *
 * @param type $utf8_string
 * @return boolean
 *
 */
function is_gsm0338($utf8_string)
{
	$gsm0338 = array(
		'@', 'Δ', ' ', '0', '¡', 'P', '¿', 'p',
		'£', '_', '!', '1', 'A', 'Q', 'a', 'q',
		'$', 'Φ', '"', '2', 'B', 'R', 'b', 'r',
		'¥', 'Γ', '#', '3', 'C', 'S', 'c', 's',
		'è', 'Λ', '¤', '4', 'D', 'T', 'd', 't',
		'é', 'Ω', '%', '5', 'E', 'U', 'e', 'u',
		'ù', 'Π', '&', '6', 'F', 'V', 'f', 'v',
		'ì', 'Ψ', '\'', '7', 'G', 'W', 'g', 'w',
		'ò', 'Σ', '(', '8', 'H', 'X', 'h', 'x',
		'Ç', 'Θ', ')', '9', 'I', 'Y', 'i', 'y',
		"\n", 'Ξ', '*', ':', 'J', 'Z', 'j', 'z',
		'Ø', "\x1B", '+', ';', 'K', 'Ä', 'k', 'ä',
		'ø', 'Æ', ',', '<', 'L', 'Ö', 'l', 'ö',
		"\r", 'æ', '-', '=', 'M', 'Ñ', 'm', 'ñ',
		'Å', 'ß', '.', '>', 'N', 'Ü', 'n', 'ü',
		'å', 'É', '/', '?', 'O', '§', 'o', 'à'
	);
	$len = mb_strlen($utf8_string, 'UTF-8');

	for ($i = 0; $i < $len; $i++)
	{
		if ( ! in_array(mb_substr($utf8_string, $i, 1, 'UTF-8'), $gsm0338))
		{
			return FALSE;
		}
	}

	return TRUE;
}

/**
 *
 * @param type $utf8_string
 * @return string:
 *  Return the coding as per gammu DB definition
 *  - Default_No_Compression
 *  - Unicode_No_Compression
 *
 */
function get_gammu_coding($utf8_string)
{
	$coding = is_gsm0338($utf8_string) ? 'Default_No_Compression' : 'Unicode_No_Compression';
	return $coding;
}
