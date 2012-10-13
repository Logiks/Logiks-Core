<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

$cfgSchema["SERVICE_ERROR_HANDLER"]=array(
		"type"=>"list",
		"values"=>array(
			"logiks"=>"Logiks Framework",
			"php"=>"PHP Core",
			)
	);
$cfgSchema["API_KEY_ENGINE"]=array(
		"type"=>"list",
		"values"=>array(
			"single"=>"Single API Key",
			"multi"=>"Multiple API Keys",
			)
	);
$cfgSchema["PRIVATE_ACCESS_ERROR"]=array(
		"type"=>"list",
		"function"=>"getAccessErrMsgList",
	);
$cfgSchema["SUPPORTED_COMMAND_FORMATS"]=array(
		"attrs"=>"readonly",
		"class"=>"readonly",
	);
$cfgSchema["SERVICE_SALT"]=array(
		"attrs"=>"readonly",
		"class"=>"readonly",
	);
$cfgSchema["DEFAULT_ACCESS_CONTROL"]=array(
		"type"=>"list",
		"values"=>array(
			"public"=>"Allow Seamless Access",
			"apikey"=>"Allow Services With API Keys Only",
			),
		"tips"=>"Default Service Access Mode For New Services."
	);
	
$cfgSchema["CFG_GROUPS"]=array(
		"Developer API Keys"=>array("API_KEY_ENGINE","API_KEY","DEFAULT_ERROR_MESSAGE"),
		"Debug/Error Handling"=>array("SERVICE_ERROR_HANDLER","SERVICE_DEBUG","SERVICE_DEBUG_TRACE","SERVICE_DEBUG_MESSAGE",),
		"Security"=>array("SUPPORTED_COMMAND_FORMATS","PRECLEAN_SERVICE_REQUEST","ALLOW_APPS_LOCAL_SERVICES","DEFAULT_ACCESS_CONTROL"),
		"Host Locking"=>array("HTTP_REFERER_LOCK","HOST_LOCK","SITE_LOCK","SERVICE_SALT"),
		"Others"=>array()
	);

if(!function_exists("getAccessErrMsgList")) {
	function getAccessErrMsgList() {
		$arr=array(
				"WrongFormat"=>"WrongFormat",
				"NotFound"=>"NotFound",
				"TypeNotFound"=>"TypeNotFound",
				"DataNotFound"=>"DataNotFound",
				"FileNotFound"=>"FileNotFound",
				"NotSupported"=>"NotSupported",
				"AccessDenial"=>"AccessDenial",
				"Blacklisted"=>"Blacklisted",
				"404"=>"404",
				"CrossSite"=>"CrossSite",
				"*"=>"*",
			);		
		return $arr;
	}
}
?>
