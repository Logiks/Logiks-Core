<?php
if(!defined('ROOT')) exit('No direct script access allowed');

//Selector/List-Tree/Table/Form
if(!function_exists("createSelector")) {
	function createSelector($data,$printTopLevels=false,
		$params=array("style"=>"")) {
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

	function createTable($data,$header=null,$named=true,$printTopLevels=true,$printHeader=true,
		$params=array("border"=>"1","cellspacing"=>"0","cellpadding"=>"0","width"=>"100%","height"=>"100%","style"=>"")) {

		$s="";
		$cols=array();
		if($printTopLevels) $s .="<table ".parseTagParams($params).">";

		if($header!=null && is_array($header) && count($header)>0) {
			if($printHeader) {
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
			} else {
				$c=count($header);
				for($i=0;$i<$c;$i++){
					$h=explodeTagData($header[$i]);
					array_push($cols,$h['value']);
				}
			}
		}
		$s .="<tbody>";
		if(isset($data)){
			$n=count($data);
			for($i=0;$i<$n;$i++){
				if($i%2==0) {
					$tr_class ="rowEven";
				}else{
					$tr_class ="rowOdd";
				}
				$s .="<tr class='$tr_class' row='$i'>";
				if(is_array($data[$i])){
					for($j=0;$j<count($data[$i]);$j++){
						$d=explodeTagData($data[$i][$j]);

						$s .="<td";
						if(strlen($d['id'])>0) $s.=" id='".$d['id']."'";
						if(strlen($d['class'])>0) $s.=" class='".$d['class']."'";
						$s.=" col='$j'";
						if($named && isset($cols[$j])) $s.=" name='".$cols[$j]."'";
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

	function createList($data,$links=array(),$classes=array(),$printTopLevels=true,$embedULinLI=true,
		$params=array("style"=>"")) {
		$s="";

		if($printTopLevels) $s .="<ul ".parseTagParams($params).">";
		if(is_array($data)) {
			$n=count($data);
			for($i=0;$i<$n;$i++){
				if(!isset($data[$i])) continue;
				if(is_array($data[$i])) {
					$xss="";
					if($embedULinLI) {
						if($i>0) {
							$s=substr(trim($s),0,strlen(trim($s))-5);
							$xss="</li>";
						}
					}
					$a=array();
					$b=array();
					if(isset($links[$i])) {
						$a=$links[$i];
					}
					if(isset($classes[$i])) {
						$b=$classes[$i];
					}
					if(count($data[$i])>0) {
						$s.=createList($data[$i],$a,$b);
					}
					$s.=$xss;
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
						$s.="<span>".toTitle($d['value'])."</span>";
					}
					$s.="</li>";
				}
			}
		}
		if($printTopLevels) $s .="</ul>";
		return $s;
	}
	function createForm($data,$layoutType="table",$printTopLevels=true,
		$middleSeparator="::",$withHelp=false,
		$params=array("border"=>"0","cellspacing"=>"0","cellpadding"=>"0","width"=>"100%","height"=>"100%","style"=>"","class"=>"formTable")) {

		$s="";
		if($layoutType!="table" && $layoutType!="list") $layoutType="table";

		if($layoutType=="table") {
			if($printTopLevels) $s .="<table ".parseTagParams($params).">";

			if(is_array($data)) {
				$i=0;
				foreach($data as $nm=>$d) {
					if($i%2==0) {
						$tr_class ="rowEven";
					}else{
						$tr_class ="rowOdd";
					}
					if(is_array($d)) {
						$fs="<tr class='{$tr_class}'><th width=150px align=left class='columnName'>%s</th><td width=20px align=center class='columnEqual'>{$middleSeparator}</td><td class='columnInput'>%s</td><td class='columnHelp' width=40px align=left>%s</td></tr>";
						if(!$withHelp) {
							$fs="<tr class='{$tr_class}'><th width=150px align=left class='columnName'>%s</th><td width=20px align=center class='columnEqual'>{$middleSeparator}</td><td class='columnInput'>%s</td></tr>";
						}
						$title="";
						$help="";
						$fld=parseFieldParams($d);

						if(isset($d['title'])) $title=$d['title'];
						else $title=toTitle($nm);
						if(isset($d['help'])) $help=parseFieldHelp($d['help']);
						if($withHelp) {
							$s.=sprintf($fs,$title,$fld,$help);
						} else {
							$s.=sprintf($fs,$title,$fld);
						}
					} else {
						if($d=="" || $d==null) {
							$s.="<tr class='{$tr_class} blankrow'><th align=left colspan=10></th></tr>";
						} elseif($d=="hbar") {
							$s.="<tr class='{$tr_class} hbar'><th align=left colspan=10><hr/></th></tr>";
						} else {
							$s.="<tr class='subheader'><th align=left colspan=10>{$d}</th></tr>";
						}
					}
					$i++;
				}
			}
			if($printTopLevels) $s .="</table>";
		} elseif($layoutType=="list") {
			if($printTopLevels) $s .="<ul ".parseTagParams($params).">";

			if(is_array($data)) {
				foreach($data as $nm=>$d) {
					if(is_array($d)) {
						$fs="<li for='{$nm}' class='nameLI'>%s</li><li for='{$nm}' class='inputLI'>%s</li><li for='{$nm}' class='helpLI'>%s</li>";
						if(!$withHelp) {
							$fs="<li for='{$nm}' class='nameLI'>%s</li><li for='{$nm}' class='inputLI'>%s</li>";
						}

						$title="";
						$help="";
						$fld=parseFieldParams($d);

						if(isset($d['title'])) $title=$d['title'];
						else $title=toTitle($nm);
						if(isset($d['help'])) $help=parseFieldHelp($d['help']);
						if($withHelp) {
							$s.=sprintf($fs,$title,$fld,$help);
						} else {
							$s.=sprintf($fs,$title,$fld);
						}
					} else {
						if($d=="" || $d==null) {
							$s.="<li>&nbsp;</li>";
						} elseif($d=="hbar") {
							$s.="<hr/>";
						} else {
							$s.="<li class='subheader'>{$d}</li>";
						}
					}
				}
			}

			if($printTopLevels) $s .="</ul>";
		} else {
			$s="Form Layout Not Supported";
		}

		return $s;
	}
}
?>
