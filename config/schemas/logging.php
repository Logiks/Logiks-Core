<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

$cfgSchema["ERROR_REPORTING"]=array(
		"type"=>"list",
		"values"=>array(
				"E_ERROR"=>"E_ERROR",
				"E_WARNING"=>"E_WARNING",
				"E_PARSE"=>"E_PARSE",
				"E_NOTICE"=>"E_NOTICE",
				"E_STRICT"=>"E_STRICT",
				"E_DEPRECATED"=>"E_DEPRECATED",
				"E_ALL"=>"E_ALL",
			)
	);
$cfgSchema["ERROR_DISP_TYPE"]=array(
		"type"=>"list",
		"values"=>array(
			"Plain"=>"plain",
			"Mordern"=>"mordern",
			"Paged"=>"errpage",
			"None"=>"none",
			)
	);
$cfgSchema["ERROR_VIEWER"]=array(
		"type"=>"list",
		"values"=>array(
			"Inline"=>"inline",
			"Block"=>"block",
			)
	);
$cfgSchema["ERROR_HANDLER"]=array(
		"type"=>"list",
		"values"=>array(
			"Logiks Framework"=>"logiks",
			"PHP Core"=>"php",
			)
	);
$this->cfgSetup["LOG_DATE"]=array(
		"type"=>"list",
		"function"=>"getDateFormatList",
	);
$this->cfgSetup["LOG_TIME"]=array(
		"type"=>"list",
		"function"=>"getTimeFormatList",
	);
$cfgSchema["LOG_FORMAT"]=array(
		"tips"=>"You may use <b>%m,%n,%N,%d,%t,%s,%S,%c,%p,%F,%f,%l,%xf,%xf,%xl,%u,%q,%Q</b>. For Details Refer Documentation.",
	);
$cfgSchema["LOG_HANDLERS"]=array(
		"type"=>"list",
		"values"=>array(
			"Console","Display","Syslog","FileLog","MailLog","SAPILog","JSConsole","JSAlert","Null"
			),
		"attrs"=>"multiple",
		"tips"=>"The ways in which system log event are handled.",
	);
$cfgSchema["LOG_EVENTS_VISITOR"]=array(
		"type"=>"list",
		"function"=>"get_LOG_EVENTS_VISITOR"
	);
$cfgSchema["LOG_NO_EVENTS_ON_ERROR"]=array(
		"tips"=>"Type In Error Codes (401,404,500, etc...) In Comma Separated.",
	);

if(!function_exists("getLogHandlers")) {
	function getLogHandlers() {
		$arr=array();
		global $CONFIG;
		$arr=$CONFIG['Log_Handlers'];
		return $arr;
	}
}
?>
