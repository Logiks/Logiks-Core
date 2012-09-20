<?php
if(!defined('ROOT')) exit('No direct script access allowed');
//Selector/List/Table
if(!function_exists("createSelector")) {	
	function createSelector($data,$printTopLevels=false,$params=array("style"=>"")) {
		$s="";	
		if($printTopLevels) $s .="<select ".parseTagParams($params).">";
		if(is_array($data)){
			$n=count($data);
			$keys=array_keys($data);
			for($i=0;$i<$n;$i++){
				$d=explodeTagData($data[$keys[$i]]);
				$s .="<option";
				if(strlen($d['id'])) $s .=" id=".$d['id'];
				if(strlen($d['class'])) $s .=" class='".$d['class']."'";
				if(strlen($d['value'])) $s .=" value='".$d['value']."'";
				$s .=">".toTitle($keys[$i]);
				$s .="</option>";
			}
		}
		if($printTopLevels) $s .="</select>";
		return $s;
	}
	
	function createTable($data,$header=null,$named=true,$printTopLevels=true,
			$params=array("border"=>"1","cellspacing"=>"0","cellpadding"=>"0","width"=>"100%","height"=>"100%","style"=>"")) {
				
			$s="";
			$cols=array();
			if($printTopLevels) $s .="<table ".parseTagParams($params).">";
			
			if($header!=null && is_array($header) && sizeOf($header)>0) {
				if($header[0]==0) {
					$s .="<thead><tr>";
					$c=count($header);
					for($i=0;$i<$c;$i++){
						$h=explodeTagData($header[$i]);				
						$s .="<th";
						if(strlen($h['id'])) $s.=" id='".$h['id']."'";
						if(strlen($h['class'])) $s.=" class='".$h['class']."'";
						$s .=">";
						$s .=toTitle($h['value']);
						$s .="</th>";
						array_push($cols,$h['value']);
					}
					$s .="</tr></thead>";
				} else {
					$keys=array_keys($header);
					$s .="<thead><tr>";
					$c=count($keys);
					for($i=0;$i<$c;$i++) {
						$h=explodeTagData($keys[$i]);
						$w=$header[$keys[$i]];
						$s .="<th";
						if(strlen($h['id'])) $s.=" id='".$h['id']."'";
						if(strlen($h['class'])) $s.=" class='".$h['class']."'";
						if(strlen($w)) $s.=" width='$w'";
						$s .=">";
						$s .=toTitle($h['value']);
						$s .="</th>";
						array_push($cols,$h['value']);
					}
					$s .="</tr></thead>";
				}
			}
			$s .="<tbody>";
			if(isset($data)){
				$n=count($data);
				for($i=0;$i<$n;$i++){
					if($i%2==0) {
						$tr_class ="row-even";
					}else{
						$tr_class ="row-odd";
					}
					$s .="<tr class='$tr_class' row='$i'>";
					if(is_array($data[$i])){
						for($j=0;$j<count($data[$i]);$j++){
							$d=explodeTagData($data[$i][$j]);
							$s .="<td";
							if(strlen($d['id'])) $s.=" id='".$d['id']."'";
							if(strlen($d['class'])) $s.=" class='".$d['class']."'";
							$s.=" col='$j'";
							if($named && (sizeOf($cols)>=$j)) $s.=" name='".$cols[$j]."'";
							$s .=">";
							$s .=$d['value'];
							$s .="</td>";		
						}
					}
					$s .="</tr>";
				}
			}
			$s .="</tbody>";
			if($printTopLevels) $s .="</table>";
			return $s;
	}
	
	function createList($data,$links,$classes,$printTopLevels=true,$embedULs=true,$params=array("style"=>"")) {
		$s="";
		
		if($printTopLevels) $s .="<ul ".parseTagParams($params).">";
		if(is_array($data)) {
			$n=count($data);
			for($i=0;$i<$n;$i++){
				if(is_array($data[$i])) {
					if($embedULs) $s=substr($s.trim(),0,strlen($s.trim())-5);
					$a=null;
					$b=null;
					if(isset($links[$i])) {
						$a=$links[$i];
					}
					if(isset($classes[$i])) {
						$b=$classes[$i];
					}
					$s.="<h2>".$data[$i][0]."</h2>";
					unset($data[$i][0]);
					if(sizeOf($data[$i][0])>0) {
						$s.=createList($data[$i],$a,$b);
					}					
					if($embedULs) $s.="</li>";
				} else {
					$d=explodeTagData($data[$i]);
					$s.="<li";
					$c="";
					if(isset($classes[$i])) {
						$c.=$classes[$i];
					}
					if(strlen($d['id'])) $s.=" id='".$d['id']."'";
					if(strlen($d['class']) || strlen($c)>0) {
						$r=$d['class']." ". $c;
						$r=trim($r);
						$s.=" class='$r'";
					}
					$s.=">";
					if(isset($links[$i]) && strlen($links[$i])>0) {
						$x=$links[$i];
						$s.="<a href='$x'>" .toTitle($d['value']). "</a>";
					} else {
						$s.=toTitle($d['value']);
					}				
					$s.="</li>";
				}			
			}
		}				
		if($printTopLevels) $s .="</ul>";
		return $s;
	}
}
?>
