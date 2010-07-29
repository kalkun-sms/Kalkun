<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	function filter_data($data) 
	{
		if($data==NULL) return "<i>Unknown</i>";
		else return $data;	
	}
	
   // convert an ascii string to its hex representation
   function AsciiToHex($ascii)
   {
      $hex = '';

      for($i = 0; $i < strlen($ascii); $i++)
         $hex .= str_pad(base_convert(ord($ascii[$i]), 10, 16), 2, '0', STR_PAD_LEFT);

      return $hex;
   }

   // convert a hex string to ascii, prepend with '0' if input is not an even number
   // of characters in length   
   function HexToAscii($hex)
   {
      $ascii = '';
   
      if (strlen($hex) % 2 == 1)
         $hex = '0'.$hex;
   
      for($i = 0; $i < strlen($hex); $i += 2)
         $ascii .= chr(base_convert(substr($hex, $i, 2), 16, 10));
   
      return $ascii;
   }	
   

	function nice_date($str, $option=NULL)
	{
		// convert the date to unix timestamp
		list($date, $time) = explode(' ', $str);
		list($year, $month, $day) = explode('-', $date);
		list($hour, $minute, $second) = explode(':', $time);

		$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
		
		$now = time();

    	$blocks = array(
			array('name'=>lang('kalkun_year'),'amount'    =>    60*60*24*365    ),
			array('name'=>lang('kalkun_month'),'amount'    =>    60*60*24*31    ),
			array('name'=>lang('kalkun_week'),'amount'    =>    60*60*24*7    ),
			array('name'=>lang('kalkun_day'),'amount'    =>    60*60*24    ),
			array('name'=>lang('kalkun_hour'),'amount'    =>    60*60        ),
			array('name'=>lang('kalkun_minute'),'amount'    =>    60        ),
			array('name'=>lang('kalkun_second'),'amount'    =>    1        )
        );
   
   		if($timestamp > $now) $string_type = ' remaining';
   		else $string_type = ' '.lang('kalkun_ago');
   		
		$diff = abs($now-$timestamp);
	   	
	   	if($option=='smsd_check')
	   	{
	   		return $diff;	
	   	}
	   	else {
		   	if($diff < 60)
		   	{
		   		return "Less than a minute ago";
		   	}
		   	else
		   	{
				$levels = 1;
				$current_level = 1;
				$result = array();
				foreach($blocks as $block)
				{
					if ($current_level > $levels) { break; }
					if ($diff/$block['amount'] >= 1)
					{
						$amount = floor($diff/$block['amount']);
						$plural = '';
						//if ($amount>1) {$plural='s';} else {$plural='';}
						$result[] = $amount.' '.$block['name'].$plural;
						$diff -= $amount*$block['amount'];
						$current_level+=1;	
					}
				}
				$res = implode(' ',$result).''.$string_type;
				return $res;
		   	}
		}	
	}   
	
	function get_modem_status($status, $tolerant)
	{
		// convert the date to unix timestamp
		list($date, $time) = explode(' ', $status);
		list($year, $month, $day) = explode('-', $date);
		list($hour, $minute, $second) = explode(':', $time);

		$timestamp = mktime($hour, $minute+$tolerant, $second, $month, $day, $year);
		
		$now = time();
		
		//$diff = abs($now-$timestamp);
		if($timestamp>$now) 
			return "connect";
		else 
			return "disconnect";
	}
   
   	function message_preview($str, $n)
   	{
   		if (strlen($str) <= $n) return $str;
		else return substr($str, 0, $n).'&#8230;';
   	}
   	
   	function compare_date_asc($a, $b)
	{
		$date1 = strtotime($a['globaldate']);
		$date2 = strtotime($b['globaldate']);
		
		if($date1 == $date2) return 0;
		return ($date1 < $date2) ? -1 : 1; 
	}

   	function compare_date_desc($a, $b)
	{
		$date1 = strtotime($a['globaldate']);
		$date2 = strtotime($b['globaldate']);
		
		if($date1 == $date2) return 0;
		return ($date1 > $date2) ? -1 : 1; 
	}	
	
	function check_delivery_report($report)
	{
		if($report=='SendingError' or $report=='Error' or $report=='DeliveryFailed'): $status = 'Sending Failed';
		elseif($report=='SendingOKNoReport'): $status = 'Sending OK - No Report';
		elseif($report=='SendingOK'): $status = 'Sending OK - Wait For Report';
		elseif($report=='DeliveryOK'): $status = 'Delivered';
		elseif($report=='DeliveryPending'): $status = 'Pending';
		elseif($report=='DeliveryUnknown'): $status = 'Unknown';
		endif;		
		
		return $status;
	}
	
	function simple_date($datetime)
	{
		list($date, $time) = explode(' ', $datetime);
		list($year, $month, $day) = explode('-', $date);		
		return $day.'/'.$month.'/'.$year.' '.$time;
	}
	
	function ByteSize($bytes) 
    {
		$size = $bytes / 1024;
		if($size < 1024)
		{
			$size = number_format($size, 2);
			$size .= ' KB';
		} 
		else 
		{
			if($size / 1024 < 1024) 
			{
				$size = number_format($size / 1024, 2);
				$size .= ' MB';
			} 
			else if ($size / 1024 / 1024 < 1024)  
			{
				$size = number_format($size / 1024 / 1024, 2);
				$size .= ' GB';
			} 
		}
		return $size;
    } 
    
    
	function get_hour()
	{
		for($i=0;$i<24;$i++) {
			$hour = $i;
			if($hour<10) $hour = "0".$hour;
			echo "<option value=\"".$hour."\">".$hour."</option>"; 
		}
	}
	
	function get_minute()
	{
		for($i=0;$i<60;$i=$i+5) {
			$min = $i;
			if($min<10) $min = "0".$min;
			echo "<option value=\"".$min."\">".$min."</option>"; 
		}
	}    

/* End of file kalkun_helper.php */
/* Location: ./system/helpers/kalkun_helper.php */
