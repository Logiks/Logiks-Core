<?php 
/*
 * Simple PHP Task Queue implementation. 
 * This belongs to PCron Package.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 24/02/2012
 * Author: Kshyana Prava kshyana23@gmail.com on 24/02/2012
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class PCronQueue {
	private $taskToRun=array();
	
	public function __construct() {
		$this->checkTasks();
	}
	
	public function run($autoEcho=false) {
		$xs=[];
		$xe=[];
		$xt=0;
		$tots=count($this->taskToRun);
		foreach ($this->taskToRun as $task) {
      if(substr($task['script_params'],0,1)=="{") {
        $tParam=json_decode($task['script_params'],true);
      } else {
        $tParam=unserialize($task['script_params']);
      }
			$tParam['appsite']=$task['site'];
			$result = $this->execute_script($task['scriptpath'], $task['method'], $tParam);
			if($result === true) {
				$xt++;
				$xs[]="{$task['title']}({$task['id']})";
				$this->mark_complete($task);
				if($autoEcho) println("Task id {$task['id']} complete.");
			} else {
				$xe[]="{$task['title']}({$task['id']})";
				if($autoEcho) println("Task id {$task['id']} not complete.");
			}
		}
		if($tots>0) {
			$sMsg="PCron Tasks $xt/$tots Were Run";
			
			if($xt<$tots) {
				_log($sMsg,"pcron",LogiksLogger::LOG_WARNING,["success"=>$xs,"error"=>$xe]);
			} else {
				_log($sMsg,"pcron",LogiksLogger::LOG_NOTICE,["success"=>$xs,"error"=>$xe]);
			}
			
			if($autoEcho) echo "$sMsg.<br/>";
			return $sMsg;
		} else {
			return "No PCron jobs schedulled.";
		}
	}
	
	public function runTaskById($taskId,$autoEcho=false) {
		$tbl=_dbTable("system_cronjobs",true);

		$data=_db(true)->_selectQ($tbl,"id,title,scriptpath,script_params,method,schedule,last_completed,run_only_once,task_md5_hash")->
							_where(["id"=>$taskId])->_get();

		$out = array();
		foreach ($data as $key => $row) {
			$out[$row["id"]] = $row;
		}
		$this->taskToRun=$out;
		return $this->run($autoEcho);
	}
	private function checkTasks($taskId=null) {
		$data=_db(true)->_selectQ(_dbTable("system_cronjobs",true),
						"id,guid,site,title,scriptpath,script_params,method,schedule,last_completed,run_only_once,task_md5_hash",
					 ["retired"=>'false',"blocked"=>'false'])->_where("(now()-last_completed>(schedule*60) OR last_completed IS NULL)");
		
		if($taskId!=null) {
			$data=$data->_where(["id"=>$taskId]);
		}
		$data=$data->_get();
    if(!$data) return [];
    
		$out = array();
		foreach ($data as $key => $row) {
			$out[$row["id"]] = $row;
		}
		$this->taskToRun=$out;
		return $out;
	}
	
  private function execute_script($script, $method, $param_array) {
		if($script==null || strlen($script)<=0) return false;
		$type="local";
		if(strstr($script,"://")) {
			$type="remote";
			if($method!="POST" && $method!="GET") {
				$method="POST";
			}
		} else {
			if($method!="POST" && $method!="GET") {
				$method="PHP";
			}
		}
		if($method=="POST" || $method=="GET") {
			if(file_exists(ROOT.PCRON_FOLDER."{$script}.php")) {
				$script="http://".PCRON_DIR_USERNAME.":".PCRON_DIR_PASSWORD."@"._server('HTTP_HOST').str_replace(_server('DOCUMENT_ROOT'),"",ROOT).PCRON_FOLDER."{$script}.php";
			} elseif(file_exists(ROOT."{$script}.php")) {
				$script="http://".PCRON_DIR_USERNAME.":".PCRON_DIR_PASSWORD."@"._server('HTTP_HOST').str_replace(_server('DOCUMENT_ROOT'),"",ROOT)."{$script}.php";
			}
		} else {
      if(file_exists(APPROOT.PCRON_FOLDER."{$script}.php")) {
        $script=APPROOT.PCRON_FOLDER."{$script}.php";
      } elseif(file_exists(APPROOT."{$script}.php")) {
        $script=APPROOT."{$script}.php";
      } elseif(file_exists(ROOT.PCRON_FOLDER."{$script}.php")) {
				$script=ROOT.PCRON_FOLDER."{$script}.php";
			} elseif(file_exists(ROOT."{$script}.php")) {
				$script=ROOT."{$script}.php";
			}
		}
		if($method=="POST" || $method=="GET") {
			$query_string = http_build_query($param_array);
			switch ($method) {
				case 'POST': 
					$urlConn = curl_init($script);
					curl_setopt ($urlConn, CURLOPT_POST, 1);
					curl_setopt ($urlConn, CURLOPT_POSTFIELDS, $query_string);  //submitting an array did not work :(
				break;
				case 'GET':                 
					$urlConn = curl_init ("$script?$query_string");
					curl_setopt ($urlConn, CURLOPT_HTTPGET, 1);
				break;      
			}

			ob_start(); // prevent the buffer from being displayed
			curl_exec($urlConn);
			$raw_response = ob_get_contents();
			ob_end_clean();
			curl_close($urlConn);       // close the connection
			//echo $raw_response;       
			$result_array = json_decode($raw_response, true);
			if(isset($result_array['status'])) {
				return $result_array['status']; 
			} else {
				return -1;
			}
		} else {
      if(!file_exists($script)) {
        return -1;
      }
			unset($_REQUEST["pcron_key"]);
      
			foreach($param_array as $a=>$b) {
				$_REQUEST[$a]=$b;
			}
			ob_start(); // prevent the buffer from being displayed
			$returned_response = include $script;
			$raw_response = ob_get_contents();
			ob_end_clean();
      
      if($returned_response==null) $returned_response=[];
      elseif(is_string($returned_response)) $returned_response=["msg"=>$returned_response];
      
      if($raw_response==null) $raw_response="{}";
      
			$result_array = json_decode($raw_response, true);
      
      $result_array=array_merge($result_array,$returned_response);
      
			if(isset($result_array['status'])) {
				return $result_array['status']; 
			} else {
				return -1;
			}
		}
  }
  
	private function mark_complete($task) {
		$task_id=$task["id"];
		$tbl1=_dbTable("system_cronjobs",true);
		$tbl2=_dbTable("log_pcron",true);
		if($task["run_only_once"]=="true") {
			$sql1 = "UPDATE $tbl1 SET last_completed = NOW(), retired='true' WHERE id = $task_id";
		} else {
			$sql1 = "UPDATE $tbl1 SET last_completed = NOW() WHERE id = $task_id";
		}
		//echo $sql1;
		
		$sql2="INSERT INTO lgks_log_pcron (id,cronid,ran_at,scriptpath,script_params,method,task_md5_hash,run_only_once) VALUES ";
		$sql2.="(0,$task_id,now(),'%s','%s','%s','%s','%s')";
		$sql2=sprintf($sql2,$task["scriptpath"],$task["script_params"],$task["method"],$task["task_md5_hash"],$task["run_only_once"]);
		//echo $sql2;
        
		_dbQuery($sql1,true);
	}
    
  public static function get_tasks($taskId=null) {
		$tbl=_dbTable("system_cronjobs",true);

		$data=_db(true)->_selectQ($tbl,"*")->
				_where(["retired"=>'false',"blocked"=>'false'])->_where("now()-last_completed>schedule");
		
		if($taskId!=null) {
			$data=$data->_where(["id"=>$taskId]);
		}
		
		$data=$data->_GET();
		
		$out = array();
		foreach ($data as $key => $row) {
			$out[$row["id"]] = $row;
		}
		return $out;
	}
    
	public static function get_all_tasks() {
		$tbl=_dbTable("system_cronjobs",true);

		$data=_db(true)->_selectQ($tbl,"*")->
		_where(["retired"=>'false',"blocked"=>'false'])->_where("now()-last_completed>schedule")->
		_orderby("site,id")->_get();

		$out = array();
		foreach ($data as $key => $row) {
			$out[$row["id"]] = $row;
		}
		return $out;
	}
    
	public static function createTask($title, $desc, $schedule, $script, $params=[], $method = 'GET', $run_only_once="false",$site=null) {
		/*if(PCronQueue::taskExists($script,$params)) {
			return "Task Exists";
		}*/
		$tbl=_dbTable("system_cronjobs",true);
		$serialized_params = serialize($params);
		$date=date("Y-m-d");
		$user="guest";
		if(isset($_SESSION["SESS_USER_ID"])) $user=$_SESSION["SESS_USER_ID"];
    
		$task_md5_hash=md5($script.$serialized_params);
		if($site==null || strlen($site)<=0) {
			if(defined("SITENAME")) $site=SITENAME;
			elseif(isset($_SESSION['LGKS_SESS_SITE'])) $site=$_SESSION['LGKS_SESS_SITE'];
			elseif(isset($_SESSION['SESS_LOGIN_SITE'])) $site=$_SESSION['SESS_LOGIN_SITE'];
			else $site="*";
		}
		
		$cols=explode(",","site, title, scriptpath, description, script_params, method, schedule, last_completed, run_only_once, task_md5_hash, retired, created_by, edited_by");
		_db(true)->_insertQ($tbl,$cols,[SITENAME, $title, $script, $desc, $serialized_params, $method, $schedule, date("Y-m-d H:i:s"), $run_only_once, $task_md5_hash, 'false', $user,  $user])->_RUN();

		return "Task Created "._db(true)->get_insertid();
	}
	
	public static function taskExists($run_script, Array $params) {
			$tbl=_dbTable("system_cronjobs",true);
			$serialized_params = serialize($params);

			$sql = "SELECT count(*) FROM $tbl WHERE task_md5_hash ='".md5($run_script.$serialized_params)."';";
			$r=_dbQuery($sql,true);
			if($r) {
				$row = _db(true)->fetchData($r);
			if($row["count(*)"]>0) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}        
	}
}
?>
