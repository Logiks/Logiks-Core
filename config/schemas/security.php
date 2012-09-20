<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

$cfgSchema["DOMAIN_CONTROLS_FLOWTYPE"]=array(
		"type"=>"list",
		"function"=>"getDomainControlFlowtype",
	);
$cfgSchema["ADMIN_APPSITES"]=array(
		"attrs"=>"readonly",
		"class"=>"readonly",
	);
$cfgSchema["HASH_SALT"]=array(
		"attrs"=>"readonly",
		"class"=>"readonly",
	);
$cfgSchema["PWD_HASH_TYPE"]=array(
		"type"=>"list",
		"tips"=>"Changing This May Result In Catastropic Problems, Do so if you know what you are doing.",
		"values"=>array(
				"md5"=>"MD5 Hash",
				"sha1"=>"SHA1 Hash",
				"lgks"=>"Logiks Hash",
			),
	);
$cfgSchema["ADMIN_USERIDS"]=array(
		"attrs"=>"readonly",
		"class"=>"readonly",
		"tips"=>"These UserIDs Can't Be Deleted or Blocked.",
	);
$cfgSchema["PERMISSION_CACHE_PERIOD"]=array(
		"tips"=>"(secs) Period After Which Permission Certificate Is Renewed.",
	);
$cfgSchema["CFG_GROUPS"]=array(
		"Domain Controls"=>array("DOMAIN_CONTROLS_ENABLE", "DOMAIN_CONTROLS_REDIRECT", "DOMAIN_CONTROLS_FLOWTYPE"),
		"Others"=>array(),
	);
if(!function_exists("getDomainControlFlowtype")) {
	function getDomainControlFlowtype() {
		return array(
				"Allow All Not Controlled"=>"appsite",
				"Allow Apps Not Controlled"=>"apps",
				"Allow Site Not Controlled"=>"site",
				"Only Allow Controlled Sites"=>"blocking",
			);
	}
}
?>
