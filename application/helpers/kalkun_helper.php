<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
	// convert the date to unix timestamp
	list($date, $time) = explode(' ', $str);
	list($year, $month, $day) = explode('-', $date);
	list($hour, $minute, $second) = explode(':', $time);

	$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
	$now = time();
	$blocks = array(
		array('name' => tr('year'), 'amount' => 60 * 60 * 24 * 365),
		array('name' => tr('month'), 'amount' => 60 * 60 * 24 * 31),
		array('name' => tr('week'), 'amount' => 60 * 60 * 24 * 7),
		array('name' => tr('day'), 'amount' => 60 * 60 * 24),
		array('name' => tr('hour'), 'amount' => 60 * 60),
		array('name' => tr('minute'), 'amount' => 60),
		array('name' => tr('second'), 'amount' => 1)
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
			return tr('Less than a minute ago'); // "Less than a minute ago"
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
				$text = tr('%nicedate% remaining');
			}
			else
			{
				$text = tr('%nicedate% ago');
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
		$status = tr('Sending failed');
	}
	elseif ($report === 'SendingOKNoReport')
	{
		$status = tr('Sent, no report');
	}
	elseif ($report === 'SendingOK')
	{
		$status = tr('Sent, waiting for report');
	}
	elseif ($report === 'DeliveryOK')
	{
		$status = tr('Delivered');
	}
	elseif ($report === 'DeliveryPending')
	{
		$status = tr('Pending');
	}
	elseif ($report === 'DeliveryUnknown')
	{
		$status = tr('Unknown');
	}
	elseif ($report === 'Reserved')
	{
		$status = tr('Not set yet');
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
