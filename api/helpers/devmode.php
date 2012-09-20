<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("__initDevMode")) {
	function __initDevMode($addr,$msg="OOPs, Down For Maintaince/Updates. Back In A Bit.") {
		if(is_array($addr)) {
			if(!in_array($_SERVER["REMOTE_ADDR"],$addr)) {
				trigger_ForbiddenError("Site Is Down For Maintenance.",
						"<h2>Coming Back Soon</h2>
						<h3>$msg</h3>"
						. "<h4>". getConfig("APPS_COMPANY") . " Team</h4>"
						. "<h4>".  date("d/m/y H:m:s")."</h4>"
						);
				//exit("<h1 style='color:maroon;font:bold 3.0em Georgia,Verdana,san-seriff;' align=center>$msg</h1>");
			}
		} else {
			if($_SERVER["REMOTE_ADDR"]!=$addr) {
				trigger_ForbiddenError("Site Is Down For Maintenance.",
						"<h2>Coming Back Soon</h2>
						<h3>$msg</h3>						"
						. "<h4>". getConfig("APPS_COMPANY") . " Team</h4>"
						. "<h4>".  date("d/m/y H:m:s")."</h4>"
						);
				//exit("<h1 style='color:maroon;font:bold 3.0em Georgia,Verdana,san-seriff;' align=center>$msg</h1>");
			}
		}
	}
	function printResponseTime() {
		$t1=$_SERVER['REQUEST_TIME'];
		$t2=time();
		$t3=abs($t2 - $t1);
		$t4=round($t3 / 60,2);
		echo "Response In $t3 secs OR $t4 minute";
	}
}
?>
