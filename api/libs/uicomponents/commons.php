<?php
if (!defined('ROOT')) exit('No direct script access allowed');
if(!function_exists("explodeTagData")) {
	function explodeTagData($value) {
		$s=$value;
		$arr = array("value"=>"","id"=>"","class"=>"",);
		
		$value=explode('@',$value);	
		if(isset($value[1])) {
			$cls =$value[1];
			if(strpos($cls,'#')>0){
				$cls=explode('#',$cls);
				
				if(isset($cls[1])) 
					$id =$cls[1]; 
				else $id="";
				$cls=$cls[0];
			}
		} else {
			 $id="";
			 $cls="";
		}
				
		if(strpos($value[0],'#')>0 || strpos($s,'#')==0){
			$value=explode('#',$value[0]);
			if(isset($value[1])) $id .=$value[1];
		}
		
		$arr['value']=$value[0];
		$arr['id']=$id;
		$arr['class']=$cls;
				
		return $arr;	
	}

	function parseTagParams($params) {
		$s="";
		foreach($params as $a=>$b) {
			$s.="$a='$b'";
		}
		return $s;
	}	
}
?>
