<?php
if(!defined('ROOT')) exit('No direct script access allowed');

//Handles Printing To Client in formats==select,table,list,json

if(!function_exists("printFormattedArray")) {
	function printFormattedArray($arr,$autoTitle=true) {
		$format="select";
		if(isset($_REQUEST["format"])) $format=$_REQUEST["format"];
		$format=strtolower($format);
		//HTML Format
		if($format=="select") {
			foreach($arr as $a=>$b) {
				echo "<option rel='$a' value='$b'>".ucwords($a)."</option>";
			}
		} elseif($format=="table") {
			foreach($arr as $a=>$b) {
				$s="<tr rel='$a'>";
				if(is_array($b)) {
					foreach($b as $x=>$y) {
						if(is_numeric($y)) {
							$s.="<td name='$x' rel='$y' align=center>".ucwords($y)."</td>";
						} elseif(is_array($y)) {
							$r=array("name"=>"$x","rel"=>"","text"=>"","align"=>"left","title"=>"");
							foreach($y as $t=>$y) {
								$r[$t]=$y;
							}
							$r['text']=ucwords($r['text']);
							$s.="<td name='{$r['name']}' rel='{$r['rel']}' title='{$r['title']}' align='{$r['align']}' >{$r['text']}</td>";
						} elseif(is_bool($y) || $y=="true" || $y=="false") {
							if($y=="true" || $y==1) 
								$s.="<td name='$x' rel='$y' align=center><input name=$x type=checkbox checked /></td>";
							else
								$s.="<td name='$x' rel='$y' align=center><input name=$x type=checkbox /></td>";
						} else {
							if(strpos("#".$y,"<")==1) {
								$s.="<td name='$x' rel='$x'>$y</td>";
							} elseif(strpos($y,"@")===0) {
								$y=substr($y,1);
								$s.="<td name='$x' rel='$x'>$y</td>";
							} else {
								$s.="<td name='$x' rel='$y'>".ucwords($y)."</td>";
							}
						}
					}
				} else {
					$s.="<td name='$a' rel='$b'>".ucwords($b)."</td>";
				}
				$s.="</tr>";
				echo $s;
			}
		} elseif($format=="list") {
			foreach($arr as $a=>$b) {
				if(is_array($b)) {
					echo "<ul>";
					printFormattedArray($b);
					echo "</ul>";
				} else {
					echo "<li rel='$a' value='$b'>".ucwords($a)."</li>";
				}
			}
		} 
		//TEXT Format
		//JSON Format
		elseif($format=="json") {
			echo json_encode($arr);
		} 
		//XML Format
		
		else {
			printArray($arr);
		}
	}
}
if(!function_exists("getMsgEnvelop")) {
	function getMsgEnvelop() {
		$envelop=array("start"=>"","end"=>"");
		
		$format="";
		if(isset($_REQUEST["format"])) $format=$_REQUEST["format"];
		if($format=="select") {$envelop["start"]="<option>"; $envelop["end"]="</option>";}
		elseif($format=="table") {$envelop["start"]="<tr a><td colspan=100 align=center>"; $envelop["end"]="</td></tr>";}
		elseif($format=="list") {$envelop["start"]="<li>"; $envelop["end"]="</li>";}
		elseif($format=="json") {$envelop["start"]="{"; $envelop["end"]="}";}
		
		return $envelop;
	}
}
if(!function_exists("println")) {
	function println($str) {
		if(is_array($str)) {
			printArray($str);
		} else echo $str . "<br/>";
	}
}
if(!function_exists("printArray")) {
	function printArray($arr) {
		echo "<pre>";
		print_r($arr);
		echo "</pre><br/>";
	}
}
?>
