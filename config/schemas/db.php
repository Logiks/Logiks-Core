<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

$cfgSchema["DB_DRIVER"]=array(
		"type"=>"list",
		"values"=>array(
				"MySQL Driver"=>"mysql",
				//"PostGreSQL Driver"=>"pgsql",
				//"SQLite Driver"=>"sqlite",
				//"Oracle Driver"=>"oracle",
				//"MSSql Driver"=>"mssql",
			),
	);
?>
