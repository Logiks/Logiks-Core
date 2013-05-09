<?php
/*
 * Simple PHP FTP Implementation.
 * Essentially, this is a client implementation for FTP Works
 *
 * Author: Kshyana Prava kshyana23@gmail.com on 18/03/2012
 * Version: 1.0
 */

class FTPClient {
	private $host,$port,$userid,$pwd,$remoteFolder;
	private $conn,$login;
	private $connected=false;

	public function __construct($userid,$pwd,$host,$port=21,$remoteFolder="/") {
		$this->host=$host;
		$this->port=$port;
		$this->userid=$userid;
		$this->pwd=$pwd;
		$this->remoteFolder=$remoteFolder;
	}
	public function __destruct() {
		$this->Close();
	}

	public function Open() {
		$this->conn=ftp_connect($this->host);
		if($this->conn) {
			$this->login=ftp_login($this->conn,$this->userid,$this->pwd);
			if($this->login) {
				ftp_pasv($this->conn, true);
			} else {
				return false;
			}
			$this->connected=true;
			return $this->conn;
		}
		return false;
	}
	public function Close() {
		if($this->isOpen()) {
			if(!ftp_close($this->conn)) {
				return false;
			} else {
				$this->connected=false;
			}
		}
		return true;
	}
	public function isOpen() {
		return ($this->conn && $this->login && $this->connected);
	}

	//All FTP Related Functions
	public function execute($cmd) {
		if(!$this->isOpen()) $this->conn=$this->open();
		return ftp_raw ($this->conn,trim($cmd));
	}

	public function pwd() {
		if(!$this->isOpen()) $this->conn=$this->open();
		return ftp_pwd($this->conn);
	}

	public function ls($path=null) {
		if(!$this->isOpen()) $this->conn=$this->open();
		if($path==null) $path=$this->pwd();
		$files=ftp_nlist($this->conn,$path);
		if(count($files) > 0) {
			return $files;
		} else {
			return FALSE;
		}
	}

	public function cd($path) {
		if(!$this->isOpen()) $this->conn=$this->open();
		if(!@ftp_chdir($this->conn,$path)) return FALSE;
		return TRUE;
	}

	public function mkdir($path) {
		if(!$this->isOpen()) $this->conn=$this->open();
		if(!@ftp_mkdir($this->conn,$path)) return FALSE;
		return TRUE;
	}

	public function rmdir($path="",$force=false) {
		if(!$this->isOpen()) $this->conn=$this->open();
		if(strlen($path) > 0) {
				$this->removeFolder($path);
		} else {
			return FALSE;
		}
	}

	public function ftpsite($path) {
		if(!$this->isOpen()) $this->conn=$this->open();
		if(!@ftp_site($this->conn, 'CHMOD 0777 '.$path)) return FALSE;
		return TRUE;
	}
	public function chmod($path,$mod="0666") {
		if(!$this->isOpen()) $this->conn=$this->open();
		/*if(!@ftp_chmod($this->conn, 0777,$path)) return FALSE;
		return TRUE; */
		if (ftp_chmod($this->conn, $mod, $path) !== false) {
			return true;
		} else {
			return false;
		}
	}

	/*THis function is used to print detail list of files*/
	public function rawlist($path) {
		if(!$this->isOpen()) $this->conn=$this->open();
		$files=ftp_rawlist($this->conn,$path);
		if(count($files) > 0) {
			return $files;
		} else {
			return FALSE;
		}
	}


	public function rename($oldpath,$newpath) {
		if(!$this->isOpen()) $this->conn=$this->open();
		if(!@ftp_rename($this->conn,$oldpath,$newpath)) return FALSE;
		return TRUE;
	}

	public function delete($path) {
		if(!$this->isOpen()) $this->conn=$this->open();
		if(!@ftp_delete($this->conn,$path)) return FALSE;
		return TRUE;
	}

	private function getFileList($local,$remote) {
		if($local==null) return null;
		$local = preg_replace("/\\/\$/", "", $local)."/";
		$remote = preg_replace("/\\/\$/", "", $remote)."/";
		$fs=array();
		if(is_dir($local)) {
			$lst=scandir($local);
			unset($lst[0]);unset($lst[1]);
			foreach($lst as $a) {
				if(is_dir($local.$a)) {
					@$this->mkdir($remote.$a);
					@$this->chmod($remote.$a,0777);

					$ls=$this->getFileList($local.$a."/",$remote.$a."/");
					$fs=array_merge_recursive($fs,$ls);
				} else {
					array_push($fs,$local.$a);
				}
			}
		}
		return $fs;
	}

	public function upload($local=".", $remote=NULL) {
		if(!$this->isOpen()){ $this->conn=$this->open();}
		if(empty($remote)) $remote=".";
		$local = preg_replace("/\\/\$/", "", $local)."/";
		$remote = preg_replace("/\\/\$/", "", $remote)."/";
		$this->mkdir($remote);
		$this->chmod($remote,0777);

		$list=array();
		$errList=array();
		$succList=array();

		$list=$this->getFileList($local,$remote);
		if($list!=null && count($list)>0) {
			foreach($list as $l) {
				$t=$remote.str_replace($local,"",$l);
				$from=@fopen($l,"r");
				$upload=false;
				if($from) {
					$this->chmod($t,0777);
					$upload=ftp_fput($this->conn,$t,$from,FTP_BINARY);
					if(!$upload) {
						$errList[$l]="Error Uploading(put) File";
					} else {
						$succList[$l]="Uploaded File To $t";
					}
				} else {
					$errList[$l]="Error Reading File";
				}
			}
		}
		$arr=array(
				"Stats"=>array("File Count"=>count($list),"Upload Count"=>count($succList),"Failed Count"=>count($errList)),
				"Details"=>array("Files"=>$list,"Uploaded"=>$succList,"Failed"=>$errList),
			);
		//$this->close();//closes the link identifier & free the resources
		return $arr;
	}


	public function download($remote="",$base,$local="") {
		if(!$this->isOpen()){ $this->conn=$this->open();}
		if(strlen($local) == 0) return FALSE;
		$local = preg_replace("/\\/\$/", "", $local)."/";
		$remote = preg_replace("/\\/\$/", "", $remote)."/";
		$base = preg_replace("/\\/\$/", "", $base)."/";

		$list=array();
		$errList=array();
		$succList=array();

		$list=$this->ls($remote);

		if($list === false) {
			return FALSE;
		}

		mkdir($local,0777,true);
		chmod($local,0777);
		if($list!=null && count($list)>0) {
			foreach($list as $a => $b) {
				if($b != "." || $b != "..") {
					echo $b."<br>";
					$size=ftp_size($this->conn,$list[$a]);
					if($size == -1) {
						$this->download($list[$a],$base,$base.$list[$a]);//ist param is remote
					} else {
						if(!ftp_get($this->conn,$base.$list[$a],$list[$a],FTP_BINARY)) {//ist param is local
							$errList[$list[$a]]= "Error Downloading(get) File";
						} else {
							$succList[$list[$a]]="Downloaded File To $local";
						}
					}
				}
			}
		}
		$arr=array(
				"Stats"=>array("File Count"=>count($list),"Down Count"=>count($succList),"Failed Count"=>count($errList)),
				"Details"=>array("Files"=>$list,"Downloaded"=>$succList,"Failed"=>$errList),
			);
			print_r($arr);
		//closes the link identifier & free the resources
	    // $this->close();
		return $arr;
	}


	public function removeFolder($path) {
		if (!(@ftp_rmdir($this->conn, $path) || $this->delete($path))) {
			$list = $this->ls($path);
			if (!empty($list)){
				foreach($list as $value){
					$this->removeFolder($value);
				}
			}
		}
		if(@ftp_rmdir($this->conn, $path)) return true;
		return false;
    }
}
?>
