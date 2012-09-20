<?php
if(!defined('ROOT')) exit('No direct script access allowed');

class AutoDataSelector {
	public function supportedTypes() {
		return array("listfile","csv","dirscan");
	}
	public function supportedFormats() {
		return array("select","li");
	}
	
	public function printDataSelector($type, $srcData, $value, $frmt="select") {
		if(strlen($type)==0 || strlen($srcData)==0) return;
		
		$s="";	
		if($type=="listfile") {
			$p=$this->getReqFile($srcData);
			if($p) {
				$ss=file_get_contents($p);
				$ss=explode("\n",$ss);
				$s=$this->printOutput($ss,$value,$frmt);
			}
		} elseif($type=="csv") {
			$ss=explode(",",$srcData);
			$s=$this->printOutput($ss,$value,$frmt);
		} elseif($type=="dirscan") {
			$ss=explode("#",$srcData);
			if(sizeOf($ss)==1) {
				$ss[1]="";
			}
			$xs=$ss[1];
			$f=$this->getReqDir($ss[0]);
			if(strlen($f)>0) {
				$fs=scandir($f);
				unset($fs[0]);unset($fs[1]);
				$ss=array();
				foreach($fs as $a) {
					if(strlen($xs>0)) {
						if(strpos($a,$xs)==(strlen($a)-strlen($xs))) {
							$a=substr($a,0,strlen($a)-strlen($xs)-1);
							array_push($ss,$a);
						}
					} else {
						array_push($ss,$a);
					}
				}
				$s=$this->printOutput($ss,$value,$frmt);
			}
		}
		if($s==null || strlen($s)==0) {
			if($value!=null && strlen($value)>0) {
				$ss=array($value);
				$s=$this->printOutput($ss,$value,$frmt);
			}			
		}
		return $s;
	}
	public function printOutput($ss,$value,$frmt="select") {
		$s="";
		if($frmt=="select" || $frmt=="ul" || $frmt=="li") {
			if($frmt=="select") $frmt="option";
			elseif($frmt=="ul" || $frmt=="li") $frmt="li";
			else $frmt="li";
			
			foreach($ss as $s1) {
				if(strlen($s1)>0) {
					if(strpos($s1,"=")>0) {
						$q1=substr($s1,0,strpos($s1,"="));
						$q2=substr($s1,strpos($s1,"=")+1);
						if($value==$q2) $s.="<$frmt value='$q2' selected>$q1</$frmt>";
						else $s.="<$frmt value='$q2'>$q1</$frmt>";
					} else {
						if($value==$s1) $s.="<$frmt selected>$s1</$frmt>";
						else $s.="<$frmt>$s1</$frmt>";
					}
				}
			}
		} elseif($frmt=="json") {
			$s=json_encode($ss);
		}
		return $s;
	}
	public function getReqDir($f) {
		$fx="";
		
		if(defined("APPROOT") && file_exists(APPROOT.$f)) $fx=APPROOT.$f;
		elseif(file_exists(ROOT.$f)) $fx=ROOT.$f;
		
		$fx=str_replace("//","/",$fx);
		return $fx;
	}
	public function getReqFile($f) {
		if(defined("APPS_MISC_FOLDER")) {
			if(file_exists(APPROOT.APPS_MISC_FOLDER.$f)) {
				return APPROOT.APPS_MISC_FOLDER.$f;
			}
		}
		if(file_exists(ROOT.MISC_FOLDER.$f)) {
				return ROOT.MISC_FOLDER.$f;
		}
		return false;
	}
}
?>
