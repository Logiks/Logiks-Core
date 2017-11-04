<?php
/*
 * Contains formating functions that formats the data in array into various formats
 * Supported Output Formats : table,select,list,text,xml,json
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("printFormattedArray")) {
	function printFormattedArray($arr,$autoTitle=true,$format=null) {
		if($format==null) {
			if(isset($_REQUEST["format"])) $format=$_REQUEST["format"];
			else $format="select";
		}

		$format=strtolower($format);
		//HTML Format
		if($format=="select") {
			if(!array_is_associative($arr)) {
				foreach($arr as $a=>$b) {
					if($autoTitle) {
						echo "<option title='$b' value='$b'>".toTitle(_ling($b))."</option>\n";
					} else {
						echo "<option title='$b' value='$b'>"._ling($b)."</option>\n";
					}
				}
			} else {
				foreach($arr as $a=>$b) {
					$xs="";
					if(is_array($b)) {
						$xx=array();
						foreach ($b as $x => $y) {
							$xx[]="$x='$y'";
						}
						$xs=implode(" ", $xx);

						if(isset($b['value'])) $b=$b['value'];
						else $b=$a;
					}
					if($autoTitle) {
						echo "<option title='$a' value='$b' $xs>".toTitle(_ling($a))."</option>\n";
					} else {
						echo "<option title='$a' value='$b' $xs>"._ling($a)."</option>\n";
					}
				}
			}
		} elseif($format=="table") {
			foreach($arr as $a=>$b) {
				$s="<tr rel='$a'>";
				if(is_array($b)) {
					foreach($b as $x=>$y) {
						if(is_numeric($y)) {
							$s.="<td name='$x' rel='$y' align=center>".toTitle(_ling($y))."</td>";
						} elseif(is_array($y)) {
							$xs="";
							foreach ($y as $u => $v) {
								$xx[]="$u='$v'";
							}
							$xs=implode(" ", $xx);

							if(isset($y['value'])) $y=$y['value'];
							else $y=$x;

							$s.="<td name='$x' rel='$y' $xs>".toTitle(_ling($y))."</td>";
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
								$s.="<td name='$x' rel='$y'>".toTitle(_ling($y))."</td>";
							}
						}
					}
				} else {
					$s.="<td name='$a' rel='$b'>".toTitle(_ling($b))."</td>";
				}
				$s.="</tr>\n";
				echo $s;
			}
		} elseif($format=="list") {
			if(!array_is_associative($arr)) {
				foreach($arr as $a=>$b) {
					if(is_array($b)) {
						echo "<ul>\n";
						printFormattedArray($b,$autoTitle,$format);
						echo "</ul>\n";
					} else {
						if($autoTitle) {
							echo "<li title='$b' value='$b'>".toTitle(_ling($b))."</li>\n";
						} else {
							echo "<li title='$b' value='$b'>"._ling($b)."</li>\n";
						}
					}
				}
			} else {
				foreach($arr as $a=>$b) {
					if(is_array($b)) {
						$xs="";
						if(is_array($b)) {
							$xx=array();
							foreach ($b as $x => $y) {
								$xx[]="$x='$y'";
							}
							$xs=implode(" ", $xx);
						}
						if($autoTitle) {
							echo "<li title='$a' $xs>".toTitle(_ling($a))."</li>\n";
						} else {
							echo "<li title='$a' $xs>"._ling($a)."</li>\n";
						}
						// echo "<ul>\n";
						// printFormattedArray($b,$autoTitle,$format);
						// echo "</ul>\n";
					} else {
						if($autoTitle) {
							echo "<li title='$a' value='$b'>".toTitle(_ling($a))."</li>\n";
						} else {
							echo "<li title='$a' value='$b'>"._ling($a)."</li>\n";
						}
					}
				}
			}
		}
		//TEXT Format
		elseif($format=="text") {
			foreach($arr as $a=>$b) {
				if(is_array($b)) {
					printFormattedArray($b,$autoTitle,$format);
				} else {
					$sx=strip_tags("$a=$b");
					echo "$sx\n";
				}
			}
		}
		//JSON Format
		elseif($format=="json") {
			echo json_encode($arr);
		}
		//XML Format
		elseif($format=="xml") {
			$arr=array_flip($arr);
			$xml = new SimpleXMLElement('<service/>');
			//arrayToXML($arrData,$xml);
			array_walk_recursive($arr, array ($xml, 'addChild'));
			echo $xml->asXML();
		}
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
		elseif($format=="table" || $format=="html") {$envelop["start"]="<tr><td colspan=100 align=center>"; $envelop["end"]="</td></tr>";}
		elseif($format=="list") {$envelop["start"]="<li>"; $envelop["end"]="</li>";}
		elseif($format=="json") {$envelop["start"]="{"; $envelop["end"]="}";}

		return $envelop;
	}
}
if(!function_exists("arrayToXML")) {
	function arrayToXML($arr, &$xml_node) {
		foreach($arr as $key => $value) {
			if(is_array($value)) {
				if(!is_numeric($key)){
					$subnode = $xml_node->addChild("$key");
					arrayToXML($value, $subnode);
				} else {
					arrayToXML($value, $xml_node);
				}
			} else {
				$xml_node->addChild("$key","$value");
			}
		}
	}
}
?>
