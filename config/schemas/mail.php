<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

$cfgSchema["SMTP_HOST"]=array(
		"type"=>"list",
		"values"=>array(
			"GMail Server"=>"smtp.gmail.com",
			"Yahoo Server"=>"smtp.mail.yahoo.com",
			"Hotmail Server"=>"smtp.live.com",
			"Hotmail Server"=>"smtp.live.com",
			"MSN Server"=>"smtp.email.msn.com",
			"Lycos Server"=>"smtp.mail.lycos.com",
			"AOL Server"=>"smtp.aol.com",
			)
	);
$cfgSchema["SMTP_PORT"]=array(
		"tips"=>"Gmail:465,587, Yahoo:995, Hotmail:587, ",
	);
$cfgSchema["MAIL_ENGINE"]=array(
		"type"=>"list",
		"values"=>array(
			"SMTP Server"=>"pear",
			"PHP Mail"=>"simple"
			)
	);
$cfgSchema["SMTP_ENCRYPTION"]=array(
		"type"=>"list",
		"values"=>array(
			"None"=>"",
			"SSL"=>"ssl",
			"TLS"=>"tls"
			)
	);
?>
