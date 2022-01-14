<?php
/*
 * ServiceController handles the way the requested command (scmd) is executed
 * based on the command type (stype) for all sync/async requests.
 * It also handles the command preprocessing and all.
 * 
 * 
 * @author : Bismay Kumar Mohapatra
 * @version : 2.0
 */
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');
//Encoding Supported : base64

include_once SERVICE_ROOT. "ServiceAPI.inc";

class ServiceController {
	private $serviceCmds=null;

	private $params=array();

	private $defaultCtrl=null;

	public function __construct() {
		$this->serviceCmds=explode(",", SUPPORTED_COMMAND_ENGINES);

		$this->defaultCtrl=array(
				"format"=>null,
				"privilege_model"=>array(),
				"access_control"=>getConfig("DEFAULT_CTRL_ACCESS"),
				"cache"=>getConfig("DEFAULT_CTRL_CACHE"),
				"debug"=>"false",
				"autoformat"=>"false",
			);
	}

	public function setupRequest($params) {
		$serviceCtrlDb=loadJSONConfig("services",$params['scmd'],MASTER_DEBUG_MODE);
		if($serviceCtrlDb) {
			$params=array_merge($params,$serviceCtrlDb);
		}
		$params=array_merge($this->defaultCtrl,$params);

		$this->params=$params;

		if($this->params['cache']==="false" || $this->params['cache']===0) $this->params['cache']=0;
		else $this->params['cache']=1;
		
		if($this->params['debug']==="false" || $this->params['debug']===0) $this->params['debug']=0;
		else $this->params['debug']=1;
		
		if($this->params['autoformat']==="false" || $this->params['autoformat']===0) $this->params['autoformat']=0;
		else $this->params['autoformat']=1;
		
		$_REQUEST['format']=$this->params['format'];
		$_REQUEST['autoformat']=$this->params['autoformat'];
		$_REQUEST['debug']=$this->params['debug'];
		
		if(!defined("SERVICE_DEBUG_MODE")) {
			if($this->params['debug']) {
				define("SERVICE_DEBUG_MODE",true);
			}
		}
		
		return true;
	}

	public function checkRequest() {
		$api=new ServiceAPI();
		$access=explode(",", $this->params['access_control']);

		if($api->isRemoteCall()) {
			if(in_array("public", $access)) {
				return true;
			} elseif(in_array("apikey", $access)) {
				if($api->checkRemoteClient()) {
					if(in_array("private", $access)) {
						if(session_check()) {
							//Check Privilege Model
							if(in_array(RoleModel::getPrivilegeHash(),$this->params['privilege_model']))
								return true;
							elseif(in_array(md5($_SESSION['SESS_USER_ID']),$this->params['privilege_model'])) 
								return true;
							elseif(checkUserScope($_REQUEST["scmd"]))
								return true;
							else
								return false;
						}
						return false;
					} elseif(in_array("protected", $access)) {
						return session_check();
					} else {
						return true;
					}
				}
				return false;
			} else {
				return false;
			}
		} else {
			if(in_array("public", $access)) {
				return true;
			} elseif(in_array("private", $access)) {
				if(session_check()) {
					//Check Privilege Model
					if(in_array(RoleModel::getPrivilegeHash(),$this->params['privilege_model']))
						return true;
					elseif(in_array(md5($_SESSION['SESS_USER_ID']),$this->params['privilege_model'])) 
						return true;
					elseif(checkUserScope($_REQUEST["scmd"]))
						return true;
					else
						return false;
				}
				return false;
			} elseif(in_array("development", $access)) {
				if(session_check()) {
					//Check Privilege Model for developer
					return (in_array(md5($_SESSION['SESS_USER_ID']),$this->params['privilege_model']) &&
							$_SESSION['SESS_PRIVILEGE_ID']<=3);
				}
				return false;
			} elseif(in_array("session", $access)) {
				return isAjax();
			} elseif(in_array("postlogin", $access)) {
				return (isAjax() && session_check());
			}
		}
		return false;
	}

	public function executeRequest() {
		$this->params['file']=$this->findServiceFile();
		
		ob_clean();
		if($this->params['file']) {
			if(file_exists($this->params['file']['src'])) {
				if($this->params['cache'] && getConfig("SERVICE_CACHE_ENABLED")) {
					echo _dataCache($this->params['file']['src']);
					return true;
				} else {
					return $this->executeFile($this->params['file']['src'],$this->params['file']['ext']);
				}
			} else {
				trigger_logikserror(906, E_USER_ERROR);
				exit();
			}
		} else {
			trigger_logikserror(904, E_USER_ERROR);
			exit();
		}
		return false;
	}
	protected function executeFile($file, $engine) {
		$engineFile=SERVICE_ROOT."engines/{$engine}.php";

		if(file_exists($engineFile)) {
			_log("Service Executed : {$this->params['scmd']}","services",LogiksLogger::LOG_NOTICE,$this->params);
			include_once $engineFile;
			return true;
		} else {
			trigger_logikserror(901, E_USER_ERROR);
		}
		return false;
	}
	public function findServiceFile() {
		$scmd=$this->params['scmd'];
		$found = checkService($scmd,$this->serviceCmds);
		if(!$found) {
			if(checkModule($scmd) && strlen($this->params['slugpath'])>0) {
				$serviceCmdPath2 = dirname(checkModule($scmd))."/actions/".$this->params['slugpath'].".php";
				if(file_exists($serviceCmdPath2)) {
					return array(
						"src"=>$serviceCmdPath2,
						"ext"=>"php2",
					);
				}
			}
		}
		return $found;
	}
}
?>
