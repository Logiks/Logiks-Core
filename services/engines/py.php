<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(function_exists("py_eval")) {
	$code=file_get_contents($file);
	py_eval($code);
} else {
	$lastLine=system("python $file", $retval);
}
?>