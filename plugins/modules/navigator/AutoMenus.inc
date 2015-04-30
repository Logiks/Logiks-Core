<?php
if(!defined('ROOT')) exit('No direct script access allowed');

class AutoMenus {
	private $enableWeights=true;
	private $printTitle=true;
	private $dbCols="id,title,category,header,hits,weight";//blocked,privilege,userid,footer,site,
	private $colDefn=array(
		"idCol"=>"id",
		"titleCol"=>"title",
		"groupCol"=>"category",
		"tipsCol"=>"header",
	);
	private $reqColDefn=null;

	private $menus=array();
	private $masterMenus=array();

	private $requiredTableList=null;
	private $requiredModuleList=null;
	private $orderBy="weight,id,category,title";//category desc,id,weight,//weight,id,category desc

	public function __construct($menus) {
		$this->menus=$menus;
		$this->reqColDefn=ArrayToList::getColumnTagsHolder();
	}

	public function printTitle($print=null) {
		if($print!==null) $this->printTitle=$print;
		return $this->printTitle;
	}
	public function requiredTableList($tableList=null) {
		if(is_array($tableList)) $this->requiredTableList=$tableList;
		return $this->requiredTableList;
	}
	public function requiredModuleList($moduleList=null) {
		if(is_array($moduleList)) $this->requiredModuleList=$moduleList;
		return $this->requiredModuleList;
	}
	public function orderBy($orderBy=null) {
		if($orderBy!=null) $this->orderBy=$orderBy;
		return $this->orderBy;

	}
	public function getSize() {
		return sizeOf($this->menus);
	}

	public function colDefns($colDefn) {
		if($colDefn!=null) {
			$this->colDefn=$colDefn;
		} else {
			$this->colDefn=ArrayToList::getColumnTagsHolder();
		}
		return $this->colDefn;
	}

	public function generateSQL($site,$pid,$sys=false) {
		foreach($this->menus as $a=>$b) {
			$b['table']=_dbtable($b['table'],$sys);
			$sql="SELECT {$this->dbCols} FROM {$b['table']}";
			$where="where (site='$site' OR site='*') AND blocked='false' AND onmenu='true'";

			if(isset($_COOKIE['USER_DEVICE'])) {
				$cols=_db($sys)->getColumnList($b['table']);
				if(array_key_exists("device",$cols)) {
					//$where.=" AND (device='*' OR device='{$_COOKIE['USER_DEVICE']}')";
					$where.=" AND (device='*' OR FIND_IN_SET('{$_COOKIE['USER_DEVICE']}',device))";
				}
			}
			if($_SESSION["SESS_PRIVILEGE_ID"]>2) {
				//$where.=" AND (privilege LIKE '%$pid,%' or privilege='*')";
				$where.=" AND (find_in_set('$pid',privilege) OR privilege='*')";
			}
			if($this->enableWeights) $order="order by ".$this->orderBy;
			else $order="";
			$sql="{$sql} {$where} {$order}";
			//echo "$sql<br/>";
			$this->menus[$a]["sql"]=$sql;
		}
	}

	public function getMainMenuArray($dbtable,$menuid,$site,$pid,$colDefs,$orderBy="",$sysdb=false) {
		if($orderBy==null || strlen($orderBy)==0) {
			$orderBy=$this->orderBy;
		}
		$sql="SELECT * from $dbtable where (site='$site' OR site='*') and blocked='false' AND onmenu='true'";
		if($menuid!="*") {
			$sql.=" AND menuid='$menuid'";
		}
		if(isset($_COOKIE['USER_DEVICE'])) {
			$cols=_db($sysdb)->getColumnList($dbtable);
			if(array_key_exists("device",$cols)) {
				//$sql.=" AND (device='*' OR device='{$_COOKIE['USER_DEVICE']}')";
				$sql.=" AND (device='*' OR FIND_IN_SET('{$_COOKIE['USER_DEVICE']}',device))";
			}
		}
		if($_SESSION["SESS_PRIVILEGE_ID"]>2) {
			//$sql.=" and (privilege LIKE '%$pid,%' or privilege='*')";
			$sql.=" AND (find_in_set('$pid',privilege) OR privilege='*')";
		}
		$sql.=" order by $orderBy";
		
		$result=_dbQuery($sql,$sysdb);
		return $this->processResult($result,$colDefs);
	}

	public function getSubmenuArrays($sys=false) {
		$groupCol=$this->colDefn["groupCol"];
		$titleCol=$this->colDefn["titleCol"];

		if(isset($this->colDefn["categoryCol"])) $categoryCol=$this->colDefn["categoryCol"]; else $categoryCol="";
		$master=array();
		foreach($this->menus as $a=>$b) {
			$sql=$b["sql"];
			$result=_dbQuery($sql,$sys);
			$out=$this->processResult($result,null,$b["lnk"],$b["icon"],$b['table']);
			if($out!=null && count($out)>0) {
				$last=array();
				foreach($out as $m=>$n) {
					$last=array_merge_recursive($last,$n);
				}
				if(isset($b['menu'])) {
					$a=$b['menu'];
					$master[sizeOf($master)][$a]=$last;
				} else {
					$master[sizeOf($master)][$a]=$last;
				}
			}
		}
		return $master;
	}

	private function processResult($result,$colDefs=null,$lnk="",$icn="",$src="") {
		if($result==null) return array();
		if($colDefs==null) {
			$groupCol=$this->colDefn["groupCol"];
			$titleCol=$this->colDefn["titleCol"];
			if(isset($this->colDefn["categoryCol"])) $categoryCol=$this->colDefn["categoryCol"]; else $categoryCol="";
			if(isset($this->colDefn["toCheckCol"])) $toCheckCol=$this->colDefn["toCheckCol"]; else $toCheckCol="";
		} else {
			$groupCol=$colDefs["groupCol"];
			$titleCol=$colDefs["titleCol"];
			if(isset($colDefs["categoryCol"])) $categoryCol=$colDefs["categoryCol"]; else $categoryCol="";
			if(isset($colDefs["toCheckCol"])) $toCheckCol=$colDefs["toCheckCol"]; else $toCheckCol="";
		}
		$toCheckCol=trim($toCheckCol);
		$out=array();
		$master=array();
		if($result) {
			$cnt=0;
			$dbData=_dbData($result);
			_db(true)->freeResult($result);
			foreach($dbData as $a=>$row) {
				$master[$row[$this->colDefn["idCol"]]]=$row[$titleCol];
			}
			foreach($dbData as $a=>$row) {
				$row['src']=$src;
				if(strlen($toCheckCol)>0) {
					if(!$this->checkMenuAcceptance($row,$toCheckCol)) continue;
				}
				if(isset($row[$this->reqColDefn["groupCol"]]) && $row[$this->reqColDefn["groupCol"]]=="/") {
					$title=$row[$this->reqColDefn["titleCol"]];
					if(!in_array($title, $this->masterMenus)) {
						$this->masterMenus[]=$title;
					}
				}

				if(!isset($row[$groupCol]) || strlen($row[$groupCol])<=0 || $row[$groupCol]=="/") $row[$groupCol]="";
				elseif(is_numeric($row[$groupCol]) && isset($master[$row[$groupCol]])) $row[$groupCol]=$master[$row[$groupCol]];
				else $row[$groupCol]="".$row[$groupCol];

				if(!isset($row[$this->reqColDefn["linkCol"]])) {
					if(isset($row[$this->colDefn["idCol"]])) {
						$row[$this->reqColDefn["linkCol"]]=sprintf($lnk,$row[$this->colDefn["idCol"]]);
					} else {
						$row[$this->reqColDefn["linkCol"]]=$lnk;
					}
				}
				$linkX=strtolower($row[$this->reqColDefn["linkCol"]]);
				if(substr($linkX,0,1)=="#" || substr($linkX,0,1)=="?" ||
					substr($linkX,0,3)=="js:" || substr($linkX,0,11)=="javascript:" ||
					substr($linkX,0,7)=="http://" || substr($linkX,0,8)=="https://" ||
					substr($linkX,0,6)=="ftp://" || substr($linkX,0,5)=="page="
					) {
					
				} elseif(substr($linkX,0,1)==":") {
					$row[$this->reqColDefn["linkCol"]]=substr($row[$this->reqColDefn["linkCol"]], 1);
				} else {
					$row[$this->reqColDefn["linkCol"]]=_link($row[$this->reqColDefn["linkCol"]]);
				}
				if(!isset($row[$this->reqColDefn["iconCol"]])) $row[$this->reqColDefn["iconCol"]]=$icn;
				if(!isset($row[$this->reqColDefn["classCol"]])) $row[$this->reqColDefn["classCol"]]="";
				if(!isset($row[$this->reqColDefn["styleCol"]])) $row[$this->reqColDefn["styleCol"]]="";
				if(!isset($row[$this->reqColDefn["targetCol"]])) $row[$this->reqColDefn["targetCol"]]="";
				if(!isset($row[$this->reqColDefn["tipsCol"]])) {
					if(isset($row[$this->colDefn["tipsCol"]])) {
						$row[$this->reqColDefn["tipsCol"]]=$row[$this->colDefn["tipsCol"]];
					} else {
						$row[$this->reqColDefn["tipsCol"]]="";
					}
				}

				$record=array("data"=>$row);

				$menuPath=$row[$groupCol];
				if(isset($row[$categoryCol]) && strlen($row[$categoryCol])>0) {
					if(strlen($menuPath)>0)
						$menuPath.="/".$row[$categoryCol];
					else
						$menuPath=$row[$categoryCol];
				}

				if(strpos($menuPath,"/")>=1) {
					$gs=str_replace("//","/",$menuPath);
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
		}
		return $out;
	}

	public function printMenuTree($treeArray,$showID=false,$dataCol="data") {
		$finalTree=array();
		foreach ($this->masterMenus as $menu) {
			$finalTree[$menu]=$treeArray[$menu];
			unset($treeArray[$menu]);
		}
		foreach ($treeArray as $menu=>$data) {
			$finalTree[$menu]=$treeArray[$menu];
			unset($treeArray[$menu]);
		}
		$treeArray=$finalTree;
		$atl=new ArrayToList();
		$atl->printTitle($this->printTitle);
		$s=$atl->getTree($treeArray,$dataCol,0,$showID);
		$s=substr($s,4,strlen($s)-9);
		echo $s;
	}

	private function checkMenuAcceptance($row,$toCheckCol) {
		//"module","dbtable","page","dbcolumn"
		$accept=true;
		if(isset($row[$toCheckCol]) && strlen($row[$toCheckCol])>0) {
			$flds=explode(",",$row[$toCheckCol]);
			foreach($flds as $toCheck) {
				if(strlen($toCheck)>0 && strpos($toCheck,"#")>2) {
					$ar=explode("#",$toCheck);
					if(count($ar)<2) {
						$accept=false;
						break;
					} else {
						if($ar[0]=="module") {
							if(strlen(checkModule($ar[1]))<=0) $accept=false;
							if(!$accept && is_array($this->requiredModuleList)) {
								if(in_array($ar[1],$this->requiredModuleList)) $accept=true;
							}
						} elseif($ar[0]=="dbtable") {
							if(is_array($this->requiredTableList)) {
								if(!in_array($ar[1],$this->requiredTableList)) $accept=false;
							}
						}
					}
				}
			}
		}
		return $accept;
	}
}
?>
