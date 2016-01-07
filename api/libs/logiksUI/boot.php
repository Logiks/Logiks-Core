<?php
/*
 * This file contains the functionality for UI Components Supported by Logiks.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 2.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("generateSelect")) {
	include_once "commons.php";
	include_once "dataSelector.php";
	include_once "arraytotree.inc";

	function generateSelect(LogiksData $ld,$searchQ="",$paramTags=null,$titleCol="title") {
		$html="";
		if($paramTags!==null) {
			$html .="<select ".parseTagParams($paramTags).">";
		}
		if($searchQ && is_string($searchQ) && strlen($searchQ)>0) {
			$data=$ld->search($searchQ);
		} else {
			$data=$ld->dump();
		}
		foreach ($data as $key => $value) {
			if(is_array($value)) {
				$attr=[];
				$title="";
				foreach ($value as $a=>$b) {
					if(is_array($b)) {
						$b=flatternArray($b);
						if(is_array($titleCol)) {
							foreach ($titleCol as $nm) {
								if(isset($b[$nm])) {
									$title=$b[$nm];
									break;
								}
							}
						} else {
							if(isset($b[$titleCol])) $title=$b[$titleCol];
						}
						$attr[]=str_replace("%20", "_", http_build_query($b," "," ",PHP_QUERY_RFC3986));
					} else {
						$attr[]="$a='$b'";
					}
				}
				$html .="<option ".implode(" ", $attr).">";
				
				if(is_array($titleCol)) {
					foreach ($titleCol as $nm) {
						if(isset($value[$nm])) {
							$html.=$value[$nm];
							break;
						}
					}
				} elseif(isset($value[$titleCol])) {
					$html.=_ling($value[$titleCol]);
				} else $html.=_ling($title);
				
				$html .="</option>";
			} else {
				$html .="<option>";
				$html.=_ling($value);
				$html .="</option>";
			}
		}
		if($paramTags!==null) {
			$html .="</select>";
		}
		return $html;
	}

	function generateList(LogiksData $ld,$searchQ="",$paramTags=null,$titleCol="title") {
		$html="";
		if($paramTags!==null) {
			$html .="<ul ".parseTagParams($paramTags).">";
		}
		if($searchQ && is_string($searchQ) && strlen($searchQ)>0) {
			$data=$ld->search($searchQ);
		} else {
			$data=$ld->dump();
		}
		foreach ($data as $key => $value) {
			if(is_array($value)) {
				$attr=[];
				$title="";
				foreach ($value as $a=>$b) {
					if(is_array($b)) {
						$b=flatternArray($b);
						if(is_array($titleCol)) {
							foreach ($titleCol as $nm) {
								if(isset($b[$nm])) {
									$title=$b[$nm];
									break;
								}
							}
						} else {
							if(isset($b[$titleCol])) $title=$b[$titleCol];
						}
						$attr[]=str_replace("%20", "_", http_build_query($b," "," ",PHP_QUERY_RFC3986));
					} else {
						$attr[]="$a='$b'";
					}
				}
				$html .="<li ".implode(" ", $attr).">";
				
				if(is_array($titleCol)) {
					foreach ($titleCol as $nm) {
						if(isset($value[$nm])) {
							$html.=$value[$nm];
							break;
						}
					}
				} elseif(isset($value[$titleCol])) {
					$html.=_ling($value[$titleCol]);
				} else $html.=_ling($title);

				$html .="</li>";
			} else {
				$html .="<li>";
				$html.=_ling($value);
				$html .="</li>";
			}
		}
		if($paramTags!==null) {
			$html .="</ul>";
		}
		return $html;
	}

	function generateTable(LogiksData $ld,$searchQ="",
					$paramTags=array("border"=>"1","cellspacing"=>"0","cellpadding"=>"0","width"=>"100%","height"=>"100%","style"=>""),
					$titleCol="title",$printHeader=true) {
		$html="";
		if($paramTags!==null) {
			$html .="<table ".parseTagParams($paramTags).">";
		}
		if($searchQ && is_string($searchQ) && strlen($searchQ)>0) {
			$data=$ld->search($searchQ);
		} else {
			$data=$ld->dump();
		}
		if($printHeader && isset($data[0])) {
			$html.="<thead><tr>";
			foreach ($data[0] as $key => $value) {
				$html.="<th class='$key' data-col='$key'>"._ling($key)."</th>";
			}
			$html.="</tr></thead>";
		}
		if($printHeader) {
			$html.="<tbody>";
		}
		foreach ($data as $key => $value) {
			if(is_array($value)) {
				$html .="<tr data-key='$key'>";
				foreach ($value as $a=>$b) {
					if(is_array($b)) {
						$title="";
						if(is_array($titleCol)) {
							foreach ($titleCol as $nm) {
								if(isset($b[$nm])) {
									$title=$b[$nm];
									break;
								}
							}
						} else {
							if(isset($b[$titleCol])) $title=$b[$titleCol];
						}
						$bx=flatternArray($b);
						$bx=str_replace("%20", "_", http_build_query($bx," "," ",PHP_QUERY_RFC3986));
						$html .="<td class='$a' data-col='$a' $bx>"._ling($title)."</td>";
					} else {
						$html .="<td class='$a' data-col='$a'>"._ling($b)."</td>";
					}
				}
				$html .="</tr>";
			} else {
				$html .="<tr><td colspan=10000>";
				$html .=_ling($value);
				$html .="</td></tr>";
			}
		}
		if($printHeader) {
			$html.="</tbody>";
		}
		if($paramTags!==null) {
			$html .="</table>";
		}
		return $html;
	}

	function generateTree(LogiksData $ld,$searchQ="",$paramTags=null,$titleCol="title") {
		$html="";
		if($paramTags!==null) {
			$html .="<ul ".parseTagParams($paramTags).">";
		}

		

		if($paramTags!==null) {
			$html .="</ul>";
		}
		return $html;
	}
	function generateTreeWithPath(LogiksData $ld,$searchQ="",$paramTags=null,$pathCol="path",$titleCol="title") {
		$html="";
		if($paramTags!==null) {
			$html .="<ul ".parseTagParams($paramTags).">";
		}



		if($paramTags!==null) {
			$html .="</ul>";
		}
		return $html;
	}
}
?>
