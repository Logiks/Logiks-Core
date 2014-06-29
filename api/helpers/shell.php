<?php
/*
 * Allows executing Linux commands within Logiks.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class Shell {
	public static function exec($cmd, $params) {
		global $SCRIPT_DIR;

		if(isset($SCRIPT_DIR)) {
			$output="";
			$pStr="";

			if(is_array($params)) {
				foreach($params as $a) {
					$pStr.=" " . $a;
				}
			} else {
				$pStr=$params;
			}
	
			if(file_exists("$SCRIPT_DIR/$cmd")) {
				$output = shell_exec("$SCRIPT_DIR/$cmd $pStr");
			} elseif(file_exists("$SCRIPT_DIR/$cmd.sh")) {
				$output = shell_exec("$SCRIPT_DIR/$cmd.sh $pStr");
			} else {
				$output = shell_exec($cmd . " $pStr");
			}

			return $output;
		} else {
			echo "<b style='color:red'>SCRIPT_DIR</b> variable not set for consolePipe.php";
			return "";
		}
	}
}
?>
