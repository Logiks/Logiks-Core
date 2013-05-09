<?php
/*
 * Simple PHP JSON DataSource Implementation
 * This belongs to PHPJson Package.
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 2012/09/15
 * Version: 1.0
 *
 * ToDo :: addFile :: Add external jsonfiles to db.
 * ToDo :: import :: Import External Files
 * ToDo :: export :: Export To External Files
 * ToDo :: updateName :: Update NameSpace From Update Meta info.
 * ToDo :: info,meta,update functions to set get other infos
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
			$this->dataDir=dirname($dataDir)."/".basename($dataDir)."/";
		} else {
			$this->dataDir=APPROOT.APPS_MISC_FOLDER."jsondb/";
		}
		if(!is_dir($this->dataDir)) {
			if(!mkdir($this->dataDir,0777,true)) {
				$this->dataDir=null;
				$this->error="Data Directory Could Not Be Found/Created.";
			} else {
				chmod($this->dataDir,0777);
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
				$ext=strtolower(end($eArr));
				if($ext=="json") {
					$name=str_replace(".json","",$f);
					$arr[$name]=$this->processData($name);
				}
			}
			$this->jsonList=$arr;
			return true;
		} else {
			$this->error="No DataDir Found";
			return false;
		}
	}
	/*All Item Functions*/
	public function getItem($name,$key,$autoCommit=false) {
		if(isset($this->jsonList[$name])) {
			if($this->jsonList[$name]["data"]==null) {
				$this->load($name);
			}
			$outArr=$this->jsonList[$name]["data"];
			if(array_key_exists($key,$outArr)) {
				return $outArr[$key];
			} else {
				$this->jsonList[$name]["data"][$key]="";
				if($autoCommit) $this->commit($name);
				return "";
			}
		} else {
			$this->error="NameSpace Not Found";
			return false;
		}
	}
	public function getItemByIndex($name,$index=0,$autoFixIndex=true) {
		if(isset($this->jsonList[$name])) {
			if($this->jsonList[$name]["data"]==null) {
				$this->load($name);
			}
			$outArr=$this->jsonList[$name]["data"];
			$keys=array_keys($outArr);

			if($autoFixIndex && $index<0) {
				$index=0;
			}
			if($autoFixIndex && $index>=count($keys)) {
				$index=count($keys)-1;
			}

			if($index>=0 && $index<count($keys)) {
				return $outArr[$keys[$index]];
			} else {
				$this->error="Wrong Index Found";
				return false;
			}
		} else {
			$this->error="NameSpace Not Found";
			return false;
		}
	}
	public function getAll($name,$infoType="data") {
		if(isset($this->jsonList[$name])) {
			if(isset($this->jsonList[$name][$infoType]))
				return $this->jsonList[$name][$infoType];
			else {
				$this->error="$infoType Is Not Supported";
				return false;
			}
		} else {
			$this->error="NameSpace Not Found";
			return false;
		}
	}
	public function setItem($name,$key,$value,$autoCommit=false) {
		if(isset($this->jsonList[$name])) {
			if($this->jsonList[$name]["data"]==null) {
				$this->load($name);
			}
			$this->jsonList[$name]["data"][$key]=$value;
			if($autoCommit) $this->commit($name);
		} else {
			$this->error="NameSpace Not Found";
			return false;
		}
	}
	public function deleteItem($name,$key,$autoCommit=true) {
		if(isset($this->jsonList[$name])) {
			if($this->jsonList[$name]["data"]==null) {
				$this->load($name);
			}
			if(isset($this->jsonList[$name]["data"][$key])) {
				unset($this->jsonList[$name]["data"][$key]);
				if($autoCommit) $this->commit($name);
				return true;
			}
		}
		$this->error="NameSpace Not Found";
		return false;
	}
	/*To Create Blank Namespace*/
	public function create($name,$info=array(),$meta=array(),$update=array(),$data=array()) {
		if(!isset($this->jsonList[$name])) {
			$this->jsonList[$name]=array(
						"file"=>"{$name}.json",
						"info"=>$info,
						"meta"=>$meta,
						"update"=>$update,
						"data"=>$data
					);
			return true;
		} else {
			$this->error="NameSpace Already Exists";
			return false;
		}
	}
	/*To Removes The Namespace Permanently*/
	public function delete($name) {
		if(isset($this->jsonList[$name])) {
			$file=$this->dataDir.$this->jsonList[$name]["file"];
			unset($this->jsonList[$name]);
			if(!is_file($file)) {
				$this->error="NameSpace File Not Found";
				return true;
			}
			if(!is_writable($file)) {
				$this->error="ReadOnly DataFile : $file";
				return false;
			}
			return unlink($file);
		}
	}
	/*To Save Changed Data*/
	public function commit($name) {
		if(isset($this->jsonList[$name])) {
			$file=$this->dataDir.$this->jsonList[$name]["file"];
			if(file_exists($file)) {
				if(!is_writable($file)) {
					$this->error="ReadOnly DataFile : $file";
					return false;
				}
			} else {
				if(!is_writable(dirname($file))) {
					$this->error="ReadOnly DataDir";
					return false;
				}
			}
			$data=$this->getCommitData($name);
			$a=file_put_contents($file,$data);
			return $a;
		}
		$this->error="NameSpace Not Found";
		return false;
	}
	/*To Load  Data from a  DataSpace Into Memory*/
	public function load($name) {
		$data=null;
		if(isset($this->jsonList[$name])) {
			$jsf=$this->dataDir.$this->jsonList[$name]["file"];
			if(file_exists($jsf)) {
				$data=file_get_contents($jsf);
				if(strlen($data)>2) {
					$data=json_decode($data,true);
				} else {
					$data=array();
				}
			} else {
				$data=$this->getCommitData();
				file_put_contents($jsf,$data);
			}
			$this->jsonList[$name]=$this->processData($name,$data);
			return true;
		} else {
			$this->create($name);
		}
		$this->error="NameSpace Not Found";
		return false;
	}
	/*To Unload a loaded DataSpace*/
	public function unload($name) {
		if(isset($this->jsonList[$name])) {
			$this->jsonList[$name]=array(
						"file"=>"{$name}.json",
						"info"=>null,
						"meta"=>null,
						"update"=>null,
						"data"=>null,
					);
			return true;
		}
		return false;
	}
	/*Other Public Functions*/
	public function listNames() {
		return array_keys($this->jsonList);
	}
	public function getError() {
		return $this->error;
	}
	public function getDataDir() {
		return $this->dataDir;
	}
	public function dump($name=null,$format=true) {
		if($format) echo "<pre>";
		if($name==null || strlen($name)<=0)
			print_r($this->jsonList);
		elseif(isset($this->jsonList[$name]))
			print_r($this->jsonList[$name]);
		if($format) echo "</pre>";
	}
	/*Private Functions*/
	private function processData($name,$data=null) {
		if($data==null) return array(
						"file"=>"{$name}.json",
						"info"=>null,
						"meta"=>null,
						"update"=>null,
						"data"=>null,
					);
		else {
			$out=array(
						"file"=>"{$name}.json",
						"info"=>array(),
						"meta"=>array(),
						"update"=>array(),
						"data"=>array(),
					);
			if(isset($data['info'])) {
				$out['info']=$data['info'];
			}

			if(isset($data['meta'])) {
				$out['meta']=$data['meta'];
			}
			if(isset($data['update'])) {
				$out['update']=$data['update'];
			}
			if(isset($data['data'])) {
				$out['data']=$data['data'];
			}
			unset($data['info']);unset($data['meta']);unset($data['update']);unset($data['data']);
			if(count($data)>0) {
				if(count($out['data'])>0) $out['data']=array_merge($out['data'],$data);
				else $out['data']=$data;
			}
			return $out;
		}
	}
	private function getCommitData($name=null) {
		if($name!=null) {
			if(isset($this->jsonList[$name])) {
				$data=$this->jsonList[$name];
				$fData=array(
					"info"=>$data['info'],
					"meta"=>$data['meta'],
					"update"=>$data['update'],
					"data"=>$data['data'],
				);
				//printArray($fData);
				return json_encode($fData);
			}
		}
		return json_encode(array(
						"info"=>array(),
						"meta"=>array(),
						"update"=>array(),
						"data"=>array(),
					));
	}
}
?>
