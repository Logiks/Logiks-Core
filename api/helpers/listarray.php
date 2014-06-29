<?php
/*
 * This contains array and tree php functions
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("printTree")) {
	
	function printTree($arr,$format="ul,li,h2,a") {
		if(!is_array($format)) $format=explode(",",$format);
		for($i=sizeOf($format);$i<=4;$i++) array_push($format,"span");
		$s="";
		foreach($arr as $a=>$b) {
			if(is_array($b)) {
				$s.="<{$format[1]}><{$format[2]}>$a</{$format[2]}>";
				$s.="<{$format[0]}>".printTree($b,$format)."</{$format[0]}>";
				$s.="</{$format[1]}>";
			} else {
				$s.="<{$format[1]}><{$format[3]} rel={$a} >$a</{$format[3]}></{$format[1]}>";
			}
		}
		return $s;
	}
	
	function printTreeForRowArray($treeArray,$dataStr="data",$colDefn=null,$format="ul,li,h2,a") {
		$ert=array();
		foreach($treeArray as $a=>$b) {
			$ert=array_merge_recursive($ert,$b);
		}
		
		$atl=new ArrayToList();
		$atl->colDefns($colDefn);
		$atl->listTags($format);
		return $atl->getTree($ert,$dataStr);
	}
}
?>
