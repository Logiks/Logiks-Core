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
		
		if($params['basedir']==null || strlen($params['basedir'])<=1) {
			if(defined("SERVICE_ROOT")) {
				$params['basedir']=dirname(getcwd())."/";
			} else {
				$params['basedir']=getcwd()."/";
			}
		}
		
 		$this->BASE_PATH=$params['basedir'];
 		$this->CURRENT_PATH=$this->BASE_PATH;
 	}
 	
 	public function __destruct() {
 	}

 	public function reset() {
 		$this->CURRENT_PATH=$this->BASE_PATH;
 	}

 	public function info($path=null) {trigger_logikserror("Current FS Driver Does Not Support This");}

 	public function pwd() {
 		return $this->CURRENT_PATH;
 	}
 	public function ls($path=null) {
 		if($path==null)
 			return scandir($this->CURRENT_PATH);
 		else
 			return scandir($path);
 	}
 	public function cd($path,$autoCreate=false) {
 		$newPath=str_replace("//", "/", $this->CURRENT_PATH.$path."/");
 		if(is_dir($newPath)) {
 			$this->CURRENT_PATH=$newPath;
 			return 1;
 		} elseif($autoCreate) {
			mkdir($newPath,0777,true);
			if(is_dir($newPath)) {
				$this->CURRENT_PATH=$newPath;
				return 1;
			} else {
				return 0;
			}
 		} else {
	 		return 0;
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
 	
 	public function copy($oldPath,$newPath) {
 		if(file_exists($oldPath)) {
 			
 		} elseif(file_exists($this->CURRENT_PATH.$oldPath)) {
 			$oldPath=$this->CURRENT_PATH.$oldPath;
 		} else {
 			return false;
 		}

 		return copy($oldPath,$newPath);
 	}
 	public function move($oldPath,$newPath) {
 		if(file_exists($oldPath)) {
 			
 		} elseif(file_exists($this->CURRENT_PATH.$oldPath)) {
 			$oldPath=$this->CURRENT_PATH.$oldPath;
 		} else {
 			return false;
 		}

 		$a=copy($oldPath,$newPath);	
 		unlink($oldPath);

 		return $a;
 	}

 	public function upload($localPath,$newPath) {
 		if(file_exists($localPath)) {
 			
 		} elseif(file_exists($this->CURRENT_PATH.$localPath)) {
 			$localPath=$this->CURRENT_PATH.$localPath;
 		} else {
 			return false;
 		}

 		if(!is_dir(dirname($newPath))) mkdir(dirname($newPath),0777,true);

 		$a=copy($localPath,$newPath);
 		unlink($localPath);

 		return $a;
 	}

 	public function chmod($path,$permissions=0755) {
 		if(file_exists($path)) {
 			
 		} elseif(file_exists($this->CURRENT_PATH.$path)) {
 			$path=$this->CURRENT_PATH.$path;
 		} else {
 			return false;
 		}

 		return chmod($path,$permissions);
 	}

	public function grep($path, $q) {trigger_logikserror("Current FS Driver Does Not Support This");}
 	public function grepName($path, $q) {trigger_logikserror("Current FS Driver Does Not Support This");}

	public function download($path,$targetDir) {trigger_logikserror("Current FS Driver Does Not Support This");}
 }
 ?>