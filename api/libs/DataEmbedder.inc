<?php
/*
 * This class is used by various other classes that intend to 
 * embed data/scripts/etc into outgoing php.
 * 
 * eg> jsphp, cssphp
 * */

class DataEmbedder {
	protected $typeOfDispatch="Tagged";//Tagged,Uncompressed,Compressed,SerializedCacheLink,SerializedCacheData
	protected $scripts;
	protected $isolatedBlocks=true;
	protected $cachePeriod=24;//hours
	protected $cacheTimeUnit="hours";
	
	protected function __construct() {
		$this->scripts=array();
		$this->typeOfDispatch="Tagged";
	}
	
	public function TypeOfDispatch($type="*") {
		if($type!="*") {			
			$this->typeOfDispatch=$type;
		}		
		return $this->typeOfDispatch;
	}
	public function IsolateBlocks($isolate="") {
		if($isolate==true || $isolate==false) {
			$this->isolatedBlocks=$isolate;
		}
		return $this->isolatedBlocks;
	}
	public function CachePeriod($hrs="*") {
		if($hrs!="*") {
			$this->cachePeriod=$isolate;
		}
		return $this->cachePeriod;
	}
	public function CacheTimeUnit($unit="*") {
		if($unit!="*") {
			$this->cacheTimeUnit=$unit;
		}
		return $this->cacheTimeUnit;
	}
	
	public function getScripts() {
		return $this->scripts;
	}
	
	public function clearScripts() {
		$this->scripts=array();
		//unset($this->scripts);
	}
	
	public function loadLibs($jslib,$vers="*") {
		$s=$this->getSerializedName($jslib,$vers);
		$p=$this->getDataPath($jslib,$vers);
		
		if($p[1]=="CDN") {
			$this->scripts[$s]=$p;
			return true;
		} elseif(file_exists($p[1])) {
			$this->scripts[$s]=$p;
			return true;
		} else {
			return false;
		}		
	}
	
	public function unloadLibs($jslib,$vers="*") {
		$s=$this->getSerializedName($jslib,$vers);
		unset($this->scripts[$s]);
	}
	
	private function getSerializedName($jslib,$vers="*") {
		$s=$jslib.$vers;
		return $s;
	}
	
	public function enumrateTime($diff) {
		$years = floor($diff / (365*60*60*24));
		$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
		$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
		$hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
		$mins = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ (60));
		$secs = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $mins*60));
		
		$data=array(
				"years"=>"$years",
				"months"=>"$months",
				"days"=>"$days",
				"hours"=>"$hours",
				"mins"=>"$mins",
				"secs"=>"$secs"
			);
		return $data;
	}
	public function debugPrint() {
		var_dump($this->scripts);
	}
	
	public function getDataPath($jslib,$vers="*") {
		if($vers=="*") $vers="";
		
		$data=array("", "", "","");
		
		return $data;
	}
	
	public function reset() {
		$this->scripts=array();
	}
	public function getCDNResource($resource,$type) {
		if(getConfig("CDN_DISABLE")=="true") {
			unset($_SESSION['CDN-RESOURCES']);
			return false;
		}
		if(!isset($_SESSION['CDN-RESOURCES'])) {
			$cdnF="";
			if(file_exists(APPROOT.JSONDB_FOLDER."cdn.json")) {
				$cdnF=APPROOT.JSONDB_FOLDER."cdn.json";
			} elseif(file_exists(ROOT.JSONDB_FOLDER."cdn.json")) {
				$cdnF=ROOT.JSONDB_FOLDER."cdn.json";
			} else {
				return false;
			}
			$data=file_get_contents($cdnF);
			$cdnSrc=json_decode($data,true);
			$_SESSION['CDN-RESOURCES']=$cdnSrc;
		}
		$cdnSrc=$_SESSION['CDN-RESOURCES'];
		if(isset($cdnSrc[$type][$resource])) {
			return $cdnSrc[$type][$resource];
		}
		return false;
	}
}
?>
