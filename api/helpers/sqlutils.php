<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('getRelation')) {
	function getRelation($func,$col,$value) {
		$r=$value;
		if(!is_numeric($value)) {
			$r="'$value'";
		}
		if($func=="eq") {
			return "$col= $r";
		} elseif($func=="ne") {
			return "$col<> $r";
		} elseif($func=="bw") {
			return "$col LIKE '$value%'";
		} elseif($func=="bn") {
			return "$col NOT LIKE '$value%'";
		} elseif($func=="ew") {
			return "$col LIKE '%$value'";
		} elseif($func=="en") {
			return "$col NOT LIKE '%$value'";
		} elseif($func=="cn") {
			return "$col LIKE '%$value%'";
		} elseif($func=="nc") {
			return "$col NOT LIKE '%$value%'";
		} elseif($func=="in") {
			return "$col LIKE '%$value%'";
		} elseif($func=="ni") {
			return "$col NOT LIKE '%$value%'";
		} elseif($func=="lt") {
			return "$col<$r";
		} elseif($func=="le") {
			return "$col<=$r";
		} elseif($func=="gt") {
			return "$col>$r";
		} elseif($func=="ge") {
			return "$col>=$r";
		} elseif($func=="nn") {
			return "$col IS NOT NULL";
		} elseif($func=="nu") {
			return "$col IS NULL";
		}
		return "$col=$value";
	}
	
	function generateSelectFromArray($arr,$paramsIn=array()) {
		$params=array(
					"table"=>"table","columns"=>"cols","where"=>"where","orderby"=>"orderby","index"=>"index","limit"=>"limit",
				);
		if(sizeOf($paramsIn)>0) {
			foreach($paramsIn as $a=>$b) {
				$params[$a]=$b;
			}
		}
		
		$sql="";
		$sql=_db()->_selectQ($arr[$params["table"]],$arr[$params["columns"]],$arr[$params["where"]],$arr[$params["orderby"]],$arr[$params["limit"]]);
		return $sql;
	}
	
	function printSQLResult($result,$dataType="json",$params=array(),$msg="") {
		if($result==null || is_bool($result)) {
			$responce->MSG=$msg;
			
			header("Content-type: application/json");
			echo json_encode($responce);
			return;
		}
		if($dataType=="json") {
			if(isset($params["page"])) $responce->page =intval($params["page"]);
			if(isset($params["total"])) $responce->total =intval($params["total"]);
			if(isset($params["records"])) $responce->records =intval($params["records"]);
			//if(isset($params["limit"])) $responce->limit =intval($params["limit"]);
			
			$i=0;
			while($row = mysql_fetch_array($result,MYSQL_NUM)) {//MYSQL_ASSOC, MYSQL_NUM, and MYSQL_BOTH
				$responce->rows[$i]['id']=$row[0];
				$responce->rows[$i]['cell']=array();
				$c=0;
				foreach($row as $a=>$b) {
					//if(is_numeric($a)) continue; if :: MYSQL_BOTH
					$type=mysql_field_type($result, $c);
					if(strlen($b)>0) {
						if($type=="date") {
							$df=str_replace("yy","Y",getConfig("DATE_FORMAT"));
							$b=_date($b,"Y/m/d",$df);
							$bT=_date("0000-00-00","Y/m/d",$df);
							if($b==$bT) {
								$b="";
							}
						} elseif($type=="time") {
							$dt=str_replace("m","i",getConfig("TIME_FORMAT"));
							$b=_time($b);
							$bT=_time("00:00:00","H:i:s",$dt);
							if($b==$bT) {
								$b="";
							}
						} elseif($type=="datetime") {
							$df=str_replace("yy","Y",getConfig("DATE_FORMAT"));
							$dt=str_replace("m","i",getConfig("TIME_FORMAT"));
							$ss=explode(" ",$b);
							$s0=_date($ss[0],"Y/m/d",$df);
							if(isset($ss[1]))
								$s1=_time($ss[1]);
							else
								$s1="";
							
							$b="$s0 $s1";
							
							$bT=_date("0000-00-00","Y/m/d",$df);
							$bT.=" "._time("00:00:00","H:i:s",$dt);
							
							if($b==$bT) {
								$b="";
							}
						} elseif($type=="blob") {
							$b="";
						}
					}
					array_push($responce->rows[$i]['cell'],$b);
					$c++;
				}
				$i++;
			}
			
			$responce->MSG=$msg;
		
			header("Content-type: application/json");
			echo json_encode($responce);
		} elseif($dataType=="xml") {
			if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml") ) {
				header("Content-type: application/xhtml+xml;charset=utf-8"); 
			} else {
				header("Content-type: text/xml;charset=utf-8");
			}
			$et = ">";
			$s = "<?xml version='1.0' encoding='utf-8'?$et\n";
			$s .= "<rows>";
			$s .= "<page>".$page."</page>";
			$s .= "<total>".$total_pages."</total>";
			$s .= "<records>".$count."</records>";
			while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
				$s.= "<row id='". $row[id]."'>";
				$i=0;
				foreach($row as $a=>$b) {
					$type  = mysql_field_type($result, $i);
					$length  = mysql_field_len($result, $i);
					if($type=="date") {
						if(strlen($b)>0) {
							$df=str_replace("yy","Y",getConfig("DATE_FORMAT"));
							$b=_date($b,"Y/m/d",$df);
							$bT=_date("0000-00-00","Y/m/d",$df);
							if($b==$bT) {
								$b="";
							}
						}
						if(strlen($b)>0) $s .= "<cell><![CDATA[". $b."]]></cell>";
					} elseif($type=="string" && $length>5) {
						if(strlen($b)>0) $s .= "<cell><![CDATA[". $b."]]></cell>";
						else $s .= "<cell></cell>";
					} else {
						$s .= "<cell>". $b ."</cell>";
					}				
					$i++;
				}
				
				$s .= "</row>";
			}
			$s .= "</rows>";
			echo $s;
		} elseif($dataType=="html") {
			if(isset($params["withheader"])) $withheader=$params["withheader"]; else $withheader="false";
			if(isset($params["multiselect"])) $multiselect=$params["multiselect"]; else $multiselect="false";
			
			if($withheader=="true" || $withheader==1) {
				$withheader=true;
			} else $withheader=false;
			if($multiselect=="true" || $multiselect==1) {
				$multiselect=true;
			} else $multiselect=false;
			
			$header="";
			$body="";
			
			if($withheader) {
				$header.="<thead>";
				$header.="<tr class='tblheader'>";
				if($multiselect) $header.="<td width=40px> -- </td>";
				while ($i < mysql_num_fields($result)) {
					$meta = mysql_fetch_field($result, $i);
					if($meta) {
						$t=str_replace("_"," ",$meta->name);
						$t=ucwords($t);			
						$header.="<td align=center>$t</td>";
					} else {
						$header.="<td>&nbsp;</td>";
					}
					$i++;
				}
				$header.="</tr>";
				$header.="</thead>";
			}
			$body.="<tbody>";
			while($row = mysql_fetch_array($result,MYSQL_NUM)) {//MYSQL_ASSOC, MYSQL_NUM, and MYSQL_BOTH
				$body.="<tr id='ROW_".$row[0]."'>";
				if($multiselect) $body.="<td align=center><input type='checkbox' rel='".$row[0]."' row='ROW_".$row[0]."' /></td>";
				$c=0;
				foreach($row as $a=>$b) {
					$meta=mysql_fetch_field($result, $c);
					$type=$meta->type;
					$name=$meta->name;
					$tbl=$meta->table;
					if($type=="date") {
						if(strlen($b)>0) {
							$df=str_replace("yy","Y",getConfig("DATE_FORMAT"));
							$b=_date($b,"Y/m/d",$df);
							$bT=_date("0000-00-00","Y/m/d",$df);
							if($b==$bT) {
								$b="";
							}
						}
					}
					if($meta->primary_key) $body.="<td col='$name' class='serial_col'>$b</td>";
					else {
						if($type=="date") {
							if($b=="NA") {
								$body.="<td col='$name' class='calerroricon' >&nbsp;</td>";
							} else {
								$body.="<td col='$name' align=center>$b</td>";
							}					
						} elseif($type=="int" || $type=="float" || $type=="double") {
							$body.="<td col='$name' align=right>$b</td>";					
						} elseif($type=="bool" || $type=="boolean") {
							$body.="<td col='$name' align=center>$b</td>";
						} elseif($type=="blob") {
							$body.="<td col='$name' class='blobicon' onclick=\"viewBlobData('$name','$tbl')\">&nbsp;</td>";
						} else {
							$body.="<td col='$name'>$b</td>";
						}				
					}
					$c++;
				}	
				$body.="</tr>";
			}
			$body.="</tbody>";
			echo "<table width=100%>$header $body</table>";
		} else {
			printArray($data);
		}
	}
	
	function printSQLResultTree($result,$colDefn=null) {
		if($colDefn==null) {
			$colDefn=array(
					"groupCol"=>"category",
					"titleCol"=>"title",
					//"categoryCol"=>"",
				);
		}
		$groupCol=$colDefn["groupCol"];
		$titleCol=$colDefn["titleCol"];
		if(isset($colDefn["categoryCol"])) $categoryCol=$colDefn["categoryCol"]; else $categoryCol="";
		if($result) {
			$out=array();
			$cnt=0;
			while($row=_db()->fetchData($result)) {
				if(strlen($row[$groupCol])<=0) $row[$groupCol]="";
				else $row[$groupCol]="".$row[$groupCol];
				
				$record=array("data"=>$row);
				if(strpos($row[$groupCol],"/")>=1) {					
					$gs=$row[$groupCol];
					if(isset($row[$categoryCol]) && strlen($row[$categoryCol])>0) {
						$gs.="/".$row[$categoryCol];
					}					
					$gs=str_replace("//","/",$gs);
					$r=explode("/",$gs);
					array_push($r,$row[$titleCol]);
					
					$arr=$record;
					$r1=array_reverse($r);
					foreach($r1 as $a) {
						$arr=array($a=>$arr);
					}
					$out[$cnt]=$arr;
				} else {
					if(strlen($row[$groupCol])<=0) {
						if(!isset($row[$categoryCol]) || strlen($row[$categoryCol])<=0) {
							$out[$cnt][$row[$titleCol]]=$record;
						} else {
							$out[$cnt][$row[$categoryCol]][$row[$titleCol]]=$record;
						}
					} else {
						if(!isset($row[$categoryCol]) || strlen($row[$categoryCol])<=0) {
							$out[$cnt][$row[$groupCol]][$row[$titleCol]]=$record;
						} else {
							$out[$cnt][$row[$groupCol]][$row[$categoryCol]][$row[$titleCol]]=$record;
						}
					}
				}
				$cnt++;
			}
			_db()->freeResult($result);
			$treeArray=array();
			foreach($out as $a=>$b) {
				$treeArray=array_merge_recursive($treeArray,$b);
			}
			/*$atl=new ArrayToList();
			$atl->colDefns($colDefn);
			$atl->listTags($format);
			$s=$atl->getTree($treeArray,"data");
			$s=substr($s,4,strlen($s)-9);
			return $s;*/
			return printTreeList($treeArray);
		}
		return "";
	}
	
	function printTreeList($treeArray) {
		if(sizeOf($treeArray)<=0) return "";
		$s="";
		foreach($treeArray as $a=>$b) {
			$data=$b['data'];
			unset($b['data']);
			if(count($b)>0) {
				$s.="<li>";
				$s.="<h3 rel='{$data['id']}'>$a</h3>";
				$s.="<ul>";
				$s.=printTree($b);
				$s.="</ul>";
				$s.="</li>";
			} else {
				$s.="<li><a rel='{$data['id']}'>$a</a></li>";
			}
		}
		return $s;
	}
}
?>
