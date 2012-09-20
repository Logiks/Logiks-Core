<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("subtractDates")) {
	function getDateTimeBlocks() {
		return array(
				'SUBDATES_SECS'=>0,
				'SUBDATES_MINS'=>1,
				'SUBDATES_HOURS'=>2,
				'SUBDATES_DAYS'=>3,
				'SUBDATES_WEEKS'=>4,
				'SUBDATES_MONTHS'=>5,
				'SUBDATES_YEARS'=>6,
		);
	}
	
	function getStdDateFormats() {
		return array(
			'DATE_ATOM'		=>	'%Y-%m-%dT%H:%i:%s%Q',
			'DATE_COOKIE'	=>	'%l, %d-%M-%y %H:%i:%s UTC',
			'DATE_ISO8601'	=>	'%Y-%m-%dT%H:%i:%s%Q',
			'DATE_RFC822'	=>	'%D, %d %M %y %H:%i:%s %O',
			'DATE_RFC850'	=>	'%l, %d-%M-%y %H:%m:%i UTC',
			'DATE_RFC1036'	=>	'%D, %d %M %y %H:%i:%s %O',
			'DATE_RFC1123'	=>	'%D, %d %M %Y %H:%i:%s %O',
			'DATE_RSS'		=>	'%D, %d %M %Y %H:%i:%s %O',
			'DATE_W3C'		=>	'%Y-%m-%dT%H:%i:%s%Q'
			);
	}		
	
	function subtractDates($older, $newer, $return = 3) {
		$dateBlocks=getDateTimeBlocks();
		$older=str_replace(getConfig("DATE_SEPARATOR"),"/",$older);
		$newer=str_replace(getConfig("DATE_SEPARATOR"),"/",$newer);
		
		if(strlen($older)<=0 || strlen($newer)<=0) return "";
		
		if(!is_numeric($older)) {
			$older=DateTime::createFromFormat(str_replace("yy","Y",getConfig("DATE_FORMAT")),$older);
			$older=strtotime($older->format('Y-m-d'));
		}
		if(!is_numeric($newer)) {
			$newer=DateTime::createFromFormat(str_replace("yy","Y",getConfig("DATE_FORMAT")),$newer);
			$newer=strtotime($newer->format('Y-m-d'));
		}
		
		$result = $newer - $older;
		switch($return) {
			case $dateBlocks['SUBDATES_MINS']:
				$result /= 60;
				break;
			case $dateBlocks['SUBDATES_HOURS']:
				$result /= 120;
				break;
			case $dateBlocks['SUBDATES_DAYS']:
				$result /= 86400;
				break;
			case $dateBlocks['SUBDATES_WEEKS']:
				$result /= 604800 ;
				break;
			case $dateBlocks['SUBDATES_MONTHS']:
				$result /= 2629743;
				break;
			case $dateBlocks['SUBDATES_YEARS']:
				$result /= 31556926;
				break;
		}
		$result=floor($result);		
		
		return $result;
	}
	
	function daysInMonth($month = 0, $year = '') {
		if ($month < 1 OR $month > 12) {
			return 0;
		}
		if ( ! is_numeric($year) OR strlen($year) != 4) {
			$year = date('Y');
		}
		if ($month == 2) {
			if ($year % 400 == 0 OR ($year % 4 == 0 AND $year % 100 != 0)) {
				return 29;
			}
		}

		$days_in_month	= array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		return $days_in_month[$month - 1];
	}
	
	function formatDateToMySQL($s) {
		if(strlen($s)<=0) return $s;
		$dt = new DateTime($s);
		return $dt->format('Y-m-d');
	}
	
	function stdDate($fmt = 'DATE_RFC822', $time = '') {
		$formats =getStdDateFormats();
		if ( ! isset($formats[$fmt])) {
			return FALSE;
		}
		$datestr=$formats[$fmt];
		$datestr = str_replace('%\\', '', preg_replace("/([a-z]+?){1}/i", "\\\\\\1", $datestr));
		
		return date($datestr, $time);
	}	
}

?>
