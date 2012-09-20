<?php
if(!function_exists("getDateFormatList")) {
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
}
?>



