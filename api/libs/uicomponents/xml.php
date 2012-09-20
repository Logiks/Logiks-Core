<?php
if (!defined('ROOT')) exit('No direct script access allowed');
if(!function_exists("createSelectorFromXML")) {
	function createSelectorFromXMLSimple($file) {
		if(!file_exists($file)) {
			return "Error:File Not Found";
			return;
		}
		$xml=simplexml_load_file($file);
		$s="";
		foreach($xml->children() as $child){
			$arr=$child->attributes();
			$s.="<option value='$arr'>"._ling($child)."</option>";
		}
		return $s;
	}
	function createSelectorFromXML($file,$xpath="//option") {//$level=0,//$attribute="",$value=""
		if(!file_exists($file)) {
			return "Error:File Not Found";
			return;
		}
		$xml=simplexml_load_file($file);
		$array=array();
		$s="";
		if(strlen($xpath)<=0) {
			$array = _processXMLNode($xml->children());
		} else {
			$result = $xml->xpath($xpath);
			if($result!=null) {
				while(list( , $node) = each($result)) {
					//$arr=$node->attributes();
					//$s.='$xpath: ',$node,"\n";
					$a=_parseNode($node);
					$array[sizeof($array)]=$a;
				}
			}
		}
		for($i=0;$i<sizeof($array);$i++) {
			//$s.=$array[$i]['name'];
			$s.="<option ";
			if(isset($array[$i]['value'])) $s.= "value='".$array[$i]['value']."'";
			if(isset($array[$i]['class'])) $s.= "value='".$array[$i]['class']."'";
			$s.= " >";
			$s.= _ling($array[$i]['name']);
			$s.= "</option>";
		}
		return $s;
	}

	function _processXMLNode($nodes) {
		$array = array();
		foreach ($nodes as $child) {			
			$array[sizeof($array)]=_parseNode($child);
		}
		return $array;
	}
	function _parseNode($child) {
		$arr=$child->attributes();
		$name="";
		$value="";
		$class="";
		if(isset($arr['name'])) {
			$name=$arr['name'];
		} else {
			$name=$child;
		}
		if(isset($arr['value'])) {
			$value=$arr['value'];
		} else $value="--";
		if(isset($arr['class'])) {
			$class=$arr['class'];
		} else $class="--";
		$a=array();
		$a['name']=$name;
		if($value!="--") $a['value']=$value;
		if($class!="--") $a['class']=$class;
		return $a;
	}
}
?>
