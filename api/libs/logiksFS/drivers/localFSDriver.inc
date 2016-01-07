<?php
/*
 * This class is used for Local File Access. This is just a basic driver. 
 * Other drivers are more sophisticated. Using this driver allows you to switch
 * between various drivers at runtime.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
 
 class localFSDriver extends LogiksFSDriver {

 	protected $keyName=array();
 	protected $params=[];

 	protected $BASE_PATH=null;
 	protected $CURRENT_PATH=null;

 	public function __construct($key,$params=null) {
 		parent::__construct($key,$params);

 		$this->BASE_PATH=getcwd()."/";
 		$this->CURRENT_PATH=getcwd()."/";
 	}
 	
 	public function __destruct() {
 	}

 	public function reset() {
 		$this->CURRENT_PATH=$this->BASE_PATH;
 	}

 	public function info($path=null) {trigger_error("Current FS Driver Does Not Support This");}

 	public function pwd() {
 		return $this->CURRENT_PATH;
 	}
 	public function ls($path=null) {
 		if($path==null)
 			return scandir($this->CURRENT_PATH);
 		else
 			return scandir($path);
 	}
 	public function cd($path) {
 		$newPath=str_replace("//", "/", $this->CURRENT_PATH.$path."/");
 		if(is_dir($newPath)) {
 			$this->CURRENT_PATH=$newPath;
 			return true;
 		} elseif(is_dir($path)) {
 			$this->CURRENT_PATH=$path;
 			return true;
 		} else {
	 		return false;
 		}
 	}
 	public function mkdir($fname,$mode=0777) {
 		$newPath=$this->CURRENT_PATH.$fname."/";
 		if(!file_exists($newPath) || !is_dir($newPath)) {
 			mkdir($newPath,$mode,true);
 		}
 		$this->cd($newPath);
 		return $newPath;
 	}
 	public function rm($path) {
 		if(file_exists($path)) {
 			
 		} elseif(file_exists($this->CURRENT_PATH.$path)) {
 			$path=$this->CURRENT_PATH.$path;
 		} else {
 			return false;
 		}

 		if(is_dir($path)) {
			$it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
			$files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
			foreach($files as $file) {
			    if ($file->isDir()){
			        rmdir($file->getRealPath());
			    } else {
			        unlink($file->getRealPath());
			    }
			}
			rmdir($path);
			return true;
		} else {
			unlink($path);
			return true;
		}
 		return false;
 	}
 	public function rename($path,$newName) {
 		if(file_exists($path)) {
 			
 		} elseif(file_exists($this->CURRENT_PATH.$path)) {
 			$path=$this->CURRENT_PATH.$path;
 		} else {
 			return false;
 		}

 		$newPath=dirname($path).DIRECTORY_SEPARATOR.$newName;
 		return rename($path, $newPath);
 	}
 	
 	public function copy($oldPath,$newPath) {trigger_error("Current FS Driver Does Not Support This");}
 	public function move($oldPath,$newPath) {trigger_error("Current FS Driver Does Not Support This");}

 	public function chmod($path,$permissions) {trigger_error("Current FS Driver Does Not Support This");}

	public function grep($path, $q) {trigger_error("Current FS Driver Does Not Support This");}
 	public function grepName($path, $q) {trigger_error("Current FS Driver Does Not Support This");}

 	public function upload($path,$file) {trigger_error("Current FS Driver Does Not Support This");}
	public function download($path,$targetDir) {trigger_error("Current FS Driver Does Not Support This");}
 }
 ?>