<?php
if(!defined('ROOT')) exit('No direct script access allowed');

class Lingulizer {
	private static $instance=null;
	var $lang=array();
	var $mismatch=null;
	var $loadedLingFiles=array();
	var $lingSeprator="=>";
	var $lingComment="#";
	var $autoFixCase=false;
	
	protected function __construct() {
		if(defined("LING_SEPARATOR_CHAR") && strlen(LING_SEPARATOR_CHAR)>0) {
			$this->lingSeprator=LING_SEPARATOR_CHAR;
		}
		if(defined("LING_COMMENT_CHAR") && strlen(LING_COMMENT_CHAR)>0) {
			$this->lingComment=LING_COMMENT_CHAR;
		}
		if(defined("LING_AUTO_FIXCASE") && LING_AUTO_FIXCASE=="true") {
			$this->autoFixCase=true;
		}
	}
	
	public static function singleton() {
		if(!isset(self::$instance)) {
			$c=__CLASS__;
			self::$instance=new $c;
		}
		return self::$instance;
	}
	
	public function loadLocaleFile($locale) {
		if(!is_array($locale)) {
			$locale=explode(",",$locale);
		}
		foreach($locale as $a) {
			$this->loadLingFile(trim($a).".ling");
		}
		return true;
	}
	
	public function loadLingFile($file) {
		if(!file_exists($file)) {
			if(defined("APPS_LING_FOLDER") && file_exists(ROOT.APPS_LING_FOLDER.$file)) {
				$file=APPROOT.APPS_LING_FOLDER.$file;
				$this->loadDataFile($file);
			} elseif(file_exists(ROOT.LING_FOLDER.$file)) {
				$file=ROOT.LING_FOLDER.$file;
				$this->loadDataFile($file);
			} else {
				return false;
			}
		} else {
			$this->loadDataFile($file);			
		}
		return true;
	}
	
	public function clearLingBase() {
		$this->lang=array();
	}
	public function lingMismatchLog($file=null) {
		if($file!=null) {
			$this->mismatch=$file;
		}
		return $this->mismatch;
	}
	protected function logMismatch($str) {
		if($this->mismatch==null) return false;
		if(!is_writable($this->mismatch)) return false;
		$str = $str."\n";
		$fh=@fopen($this->mismatch,"a+");
		@fwrite($fh,$str);
		@fclose($fh);
		return true;
	}
	protected function loadDataFile($file) {
		if(!file_exists($file)) {
			return false;
		}
		$fh=@fopen($file,"r");
		if($fh){
			while(($buffer = fgets($fh, 8192)) !== false){
				$buffer=str_replace("\n","",$buffer);
				if(strlen($buffer)>0) {
					$buffer=ltrim($buffer);
					if(strpos($buffer,$this->lingComment)!==0) {
						if(strpos($buffer,$this->lingSeprator)>1) {
							$arr=explode($this->lingSeprator,$buffer);
							$this->lang[strtolower($arr[0])]=$arr[1];
						}
					}
				}
			}
			array_push($this->loadedLingFiles,$file);
			return true;
		}
		return false;
	}
	
	public function toLing($str) {
		$s=strtolower($str);
		if(in_array($s, array_keys($this->lang))) {
			if($this->autoFixCase) {
				$trans=$this->fixCase($this->lang[$s],$str);
				return $trans;
			} else {
				return $this->lang[$s];
			}
		}else{
			@$this->logMismatch($str);
			return $str;
		}
	}
	
	public function toLingContent($str) {
		while(strstr($str, '  ')) {
			$str = str_replace('  ', ' ', $str);
		}
		$str = explode(' ', $str);
		for($i=0;$i<count($str);$i++){
			$replacement=$str[$i];
			$trans=$this->toLing($replacement);
			$str[$i]=$trans;
		}
		return implode(' ',$str);
	}	
	
	public function isLingFileLoaded($file) {
		return in_array($file,$this->loadedLingFiles);
	}
	
	public function getSize() {
		if($this->lang==null) return 0;
		else return sizeOf($this->lang);
	}
	
	public function lingSeparator($txt=null) {
		if($txt!=null && strlen($txt)>0) {
			$this->lingSeprator=$txt;
		}
		return $this->lingSeprator;
	}
	public function lingComment($txt=null) {
		if($txt!=null && strlen($txt)>0) {
			$this->lingComment=$txt;
		}
		return $this->lingComment;
	}
	public function lingAutoFixCase($fix=null) {
		if($txt!=null && strlen($txt)>0) {
			if($fix || $fix=="true") $this->autoFixCase=true;
			else $this->autoFixCase=false;
		}
		return $this->autoFixCase;
	}
	
	private function fixCase($word,$ref) {
		$n=ord($ref[0]);
		if($n>=65 && $n<=90) {
			$word=ucwords($word);
		}
		return $word;
	}
}
/*
 $ling=new lingulizer();
$arr=$ling->loadLingFile("language.ling");
//print_r($arr);
//$str=$ling->toLang("y");
//print_r($str);
$replaced=$ling->toLingContent("There is someone yet to come.Please add guest list by 1");
print_r($replaced);
*/
?>
