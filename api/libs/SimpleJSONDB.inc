<?php
/*
 * Simple PHP JSON DataSource Implementation
 * This belongs to PHPJson Package.
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 2012/09/15
 * Version: 1.0
 * 
 * USAGE
	$jsondb=new SimpleJSONDB(dirname(__FILE__)."/data/");

	$jsondb->load("test");
	//$jsondb->add("test",array("W"=>"m","U"=>"Hypo"));
	//$jsondb->delete("test",4);
	//$jsondb->update("test",4,array("Q"=>"W","R"=>false));
	printArray($jsondb->fetchByIndex("test"));
 */
class SimpleJSONDB {
	
	private $dataDir=null;
	private $error="";
	
	private $jsonList=array();
	
	public function __construct($dataDir=null) {
		if($dataDir!=null) {
			$this->dataDir=$dataDir;
		} else {
			$this->dataDir=APPROOT.APPS_MISC_FOLDER."jsondb/";
		}
		if(!is_dir($this->dataDir)) {
			if(!mkdir($this->dataDir,0666,true)) {
				$this->dataDir=null;
				$this->error="Data Directory Could Not Be Found/Created.";
			}
		}
		$this->reloadNames();
	}
	public function reloadNames() {
		if($this->dataDir!=null) {
			$fs=scandir($this->dataDir);
			unset($fs[0]);unset($fs[1]);
			$arr=array();
			foreach($fs as $f) {
				$eArr=explode(".",$f);
				$ext="";
				if(count($eArr)>0) {
					$exts=strtolower($eArr[count($eArr)-1]);
					unset($eArr[count($eArr)-1]);
					$ext=$exts;
				}
				if($ext=="json") {
					$arr[implode(".",$eArr)]=array(
						"file"=>$f,
						"data"=>null
					);
				}
			}
			$this->jsonList=$arr;
			return true;
		} else {
			return null;
		}
	}
	public function listNames() {
		return array_keys($this->jsonList);
	}
	public function add($name,$arr=array()) {
		if(!isset($this->jsonList[$name])) {
			$this->jsonList[$name]=array(
						"file"=>"{$name}.json",
						"data"=>array()
					);
		}
		if(isset($this->jsonList[$name])) {
			if($this->jsonList[$name]["data"]==null) {
				$this->load($name);
			}
			if($arr!=null) {
				array_push($this->jsonList[$name]["data"],$arr);
				$this->commit($name);
			}
			return true;
		} else {
			return false;
		}
	}
	public function delete($name,$index) {
		if(isset($this->jsonList[$name])) {
			if($this->jsonList[$name]["data"]==null) {
				$this->load($name);
			}
			if(isset($this->jsonList[$name]["data"][$index])) {
				unset($this->jsonList[$name]["data"][$index]);
				$this->commit($name);
				return true;
			}
		}
		return false;
	}
	public function update($name,$index,$arr) {
		if(isset($this->jsonList[$name]) && $arr!=null) {
			if($this->jsonList[$name]["data"]==null) {
				$this->load($name);
			}
			if(isset($this->jsonList[$name]["data"][$index])) {
				$this->jsonList[$name]["data"][$index]=$arr;
				$this->commit($name);
				return true;
			} else {
				$this->jsonList[$name]["data"][$index]=$arr;
				$this->commit($name);
				return true;
			}
		}
		return false;
	}
	public function fetchByIndex($name,$index=-1) {
		if(isset($this->jsonList[$name])) {
			if($this->jsonList[$name]["data"]==null) {
				$this->load($name);
			}
			$outArr=$this->jsonList[$name]["data"];
			if($index<0)
				return $outArr;
			else {
				if(array_key_exists($index,$outArr)) {
					$outArr=$this->jsonList[$name]["data"][$index];
					return array($index=>$outArr);
				} else {
					return array();
				}
			}
				
		}
		return false;
	}
	private function commit($name) {
		if(isset($this->jsonList[$name])) {
			$file=$this->dataDir.$this->jsonList[$name]["file"];
			$data=$this->jsonList[$name]["data"];
			
			file_put_contents($file,json_encode($data));
			return true;
		}
		return false;
	}
	public function load($name) {
		if(isset($this->jsonList[$name])) {
			$data=file_get_contents($this->dataDir.$this->jsonList[$name]["file"]);
			if(strlen($data)>2) {
				$data=json_decode($data,true);
				$this->jsonList[$name]["data"]=$data;
			} else {
				$this->jsonList[$name]["data"]=array();
			}
			return true;
		}
		return false;
	}
	private function unload($name) {
		if(isset($this->jsonList[$name])) {
			$this->jsonList[$name]=array(
						"file"=>"{$name}.json",
						"data"=>null
					);
			return true;
		}
		return false;
	}
	public function getError() {
		return $this->error;
	}
	public function getDataDir() {
		return $this->dataDir;
	}
}
?>
