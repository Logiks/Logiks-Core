<?php
/*
 * Date related operations. They contain functions like
 * add,subtract, time diff simmilar to '2 months ago'
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("subtractDates")) {
	function getDateFormatList() {
		return array(
					"d/m/yy","m/d/yy","yy/m/d","yy/d/m"
				);
	}
	function getTimeFormatList() {
		return array(
					"H:i:s","h:i:s","H:i:s:u","h:i:s:u","G:i:s","g:i:s","G:i:s:u","g:i:s:u"
				);
	}

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
	function subtractDatesToStr($firstTime,$lastTime,$level=10,$autoFill=true) {
		$firstTime = str_replace("/","-",$firstTime);
	    $lastTime = str_replace("/","-",$lastTime);

	    // convert to unix timestamps
	    $firstTime=strtotime($firstTime);
	    $lastTime=strtotime($lastTime);

	    $difference = 0;

	    // perform subtraction to get the difference (in seconds) between times 
	    if($firstTime > $lastTime) {
	        $difference = $firstTime - $lastTime;
	    } else {
	        $difference = $lastTime - $firstTime;
	    }   

	    //$difference=$lastTime-$firstTime;
	    $period[] = abs(floor($difference / 31536000));//years
	    //$period[] = abs(floor($difference / 31536000));//months
	    $period[] = abs(floor(($difference-($period[0] * 31536000))/86400));//days
	    $period[] = abs(floor(($difference-($period[0] * 31536000)-($period[1] * 86400))/3600));//hours
	    $period[] = abs(floor(($difference-($period[0] * 31536000)-($period[1] * 86400)-($period[2] * 3600))/60));//mins
	    $period[] = abs(floor(($difference-($period[0] * 31536000)-($period[1] * 86400)-($period[2] * 3600)-($period[3]*60))));//secs
	    
	    $names=array("Years","Days","Hours","Mins","Secs");
	    $str="";
	    if($level>count($period)) {
	        $level=count($period);
	    }
	    $str_arr = array();
	    for($i=0;$i<$level;$i++) { 
	        $str_arr[] = $period[$i]." ".$names[$i];
	    }
	    $str = implode(", ",$str_arr);
	    
	    return $str;
	}
	function relativeDate($firstTime,$lastTime) {
		// convert to unix timestamps
		$firstTime=strtotime($firstTime);
		$lastTime=strtotime($lastTime);
		
		// perform subtraction to get the difference (in seconds) between times
		$difference=$lastTime-$firstTime;
		
		if($difference==0) return "now";
		
		$period[] = abs(floor($difference / 31536000));//years
		//$period[] = abs(floor($difference / 31536000));//months
		$period[] = abs(floor(($difference-($period[0] * 31536000))/86400));//days
		$period[] = abs(floor(($difference-($period[0] * 31536000)-($period[1] * 86400))/3600));//hours
		$period[] = abs(floor(($difference-($period[0] * 31536000)-($period[1] * 86400)-($period[2] * 3600))/60));//mins
		$period[] = abs(floor(($difference-($period[0] * 31536000)-($period[1] * 86400)-($period[2] * 3600)-($period[3]*60))));//secs
		
		$names=array("Year","Day","Hour","Min","Sec");
		
		//$str="";
		//if($level>count($period)) $level=;
		for($i=0;$i<count($period);$i++) {
			if($period[$i]>0) {
				if($period[$i]<=1)
					$str=$period[$i]." ".$names[$i]." ago";
				else
					$str=$period[$i]." ".$names[$i]."s ago";
				break;
			}
		}
		return $str;
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

	//Converts input time into date in standard date time format.
	function stdDate($time = null, $fmt = 'DATE_RFC822') {
		if($time==null) $time=time();

		$formats =getStdDateFormats();
		if (!isset($formats[$fmt])) {
			return FALSE;
		}
		$datestr=$formats[$fmt];
		$datestr = str_replace('%\\', '', preg_replace("/([a-z]+?){1}/i", "\\\\\\1", $datestr));

		return date($datestr, $time);
	}
	function enumrateTime($diff) {
		$years = floor($diff / (365*60*60*24));
		$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
		$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
		$hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
		$mins = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ (60));
		$secs = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $mins*60));

		$data=array(
				"years"=>"$years",
				"months"=>"$months",
				"days"=>"$days",
				"hours"=>"$hours",
				"mins"=>"$mins",
				"secs"=>"$secs"
			);
		return $data;
	}
}

?>
