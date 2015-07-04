<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

$cfgSchema["AUTO_PCRON_PERIOD"]=array(
		"tips"=>"(Sec) Period In Seconds Between 2 Cron Attempts In Same Session.",
	);
$cfgSchema["PCRON_KEY"]=array(
		"tips"=>"This key is used to access/run pcron facilities from remote party.",
	);
$cfgSchema["API_KEY"]=array(
		"tips"=>"This key is used to access services/* from remote/3rd party.",
	);
$cfgSchema["CFG_GROUPS"]=array(
		"PCron Settings"=>array("ENABLE_AUTO_PCRON","AUTO_PCRON_PERIOD","PCRON_KEY","PCRON_DIR_USERNAME","PCRON_DIR_PASSWORD"),
		"Hook Settings"=>array("ENABLE_HOOKS"),
		"Others"=>array(),
	);
?>
