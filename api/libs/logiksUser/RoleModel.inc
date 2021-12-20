<?php
/*
 * This class contains the Privilege/Permission/Role Model based permission system
 * for all the logical inputs within Logiks Framework
 * 
 * DB Column Mapping
 * module   -> $module
 * category -> $actionType
 * activity -> $activity
 * 
 * Policy String => {$module}.{$actionType}.{$activity}
 * Function Flow => checkUserPolicy->checkUserScope->checkUserRoles
 * 
 * Version Details
 * 1.0    Basic Table
 * 2.0    With Policy String, RoleModelHash Columns
 * 
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class RoleModel {
	
  private static $instance=null;
  private $version = "1.0";//1.0,2.0
  
  private $roleTable = null;
	
  public static function getInstance() {
    if(RoleModel::$instance==null) {
      RoleModel::$instance=new RoleModel();
    }
    return RoleModel::$instance;
  }

  public function __construct() {
    $this->roleTable = _dbTable("rolemodel",true);
    
    if(!isset($_SESSION["ROLEMODEL_VERSION"])) {
      $columns = _db(true)->get_columnList($this->roleTable);
      if(in_array("policystr",$columns)) {
        $_SESSION["ROLEMODEL_VERSION"] = "2.0";
      } else {
        $_SESSION["ROLEMODEL_VERSION"] = "1.0";
      }
    }
    $this->version = $_SESSION["ROLEMODEL_VERSION"];
    
    if(!isset($_SESSION["ROLEMODEL"])) {
      $_SESSION["ROLEMODEL"] = [];
    }
    if(!isset($_SESSION["ROLESCOPE"])) {
      $_SESSION["ROLESCOPE"] = [];
    }
    if(!isset($_SESSION["ROLESCOPEMAP"])) {
      $_SESSION["ROLESCOPEMAP"] = [];
    }
    if(!isset($_SESSION["ROLESGLOBAL"][SITENAME])) {
      $this->loadScopeRules();
    }
  }
  
  //Checks the global policy that came with application and returns
	// -1 Not defined in the global scope
	//  0	Global scope is found, but no matching condition, so not allowed
	//  1	Allowed in global policy
	public function checkGlobals($policyStr,$reloadScopes=false) {
    	if(!isset($_SESSION['SESS_GUID'])) $_SESSION['SESS_GUID']="guest";
		
		if(isset($_SESSION["SESS_PRIVILEGE_ID"]) && $_SESSION["SESS_PRIVILEGE_ID"]<=ROLE_PRIME) {
			return 1;
		}
		
		if($reloadScopes) {
			unset($_SESSION["ROLESGLOBAL"][SITENAME]);
		}
		$gScopes=$this->getGlobalScopes();

		$policyStr = $this->cleanScopePolicy($policyStr);
		$policyStr=strtolower($policyStr);
    
		$policyStrArr = explode(".",$policyStr);
		
		if(isset($gScopes[$policyStr])) {
			
		} elseif(isset($policyStrArr[1]) && isset($gScopes["{$policyStrArr[0]}.{$policyStrArr[1]}"])) {
			$policyStr="{$policyStrArr[0]}.{$policyStrArr[1]}";
		} elseif(isset($gScopes[$policyStrArr[0]])) {
			$policyStr=$policyStrArr[0];
		}

		if(isset($gScopes[$policyStr])) {
			if(!isset($gScopes[$policyStr]['access_control'])) {
				$gScopes[$policyStr]['access_control']="public";
			}
			switch($gScopes[$policyStr]['access_control']) {
				case "public":
					return 1;
				break;
				case "private":
					if(isset($gScopes[$policyStr]['users'])) {
						if(in_array($_SESSION['SESS_USER_ID'],$gScopes[$policyStr]['users'])) {
							return 1;
						}
					} elseif(isset($gScopes[$policyStr]['privileges'])) {
						if(in_array($_SESSION["SESS_PRIVILEGE_NAME"],$gScopes[$policyStr]['privileges'])) {
							return 1;
						} elseif(in_array(RoleModel::getPrivilegeHash(),$gScopes[$policyStr]['privileges'])) {
							return 1;
						} else {
							$priArr = RoleModel::getRoleList();
							foreach($priArr as $row) {
								if(in_array($row['role_name'],$gScopes[$policyStr]['privileges'])) {
									return 1;
								}
							}
						}
					} else {
						return 0;
					}
				break;
			}
			return 0;
		} else {
			return -1;
		}
  }
  
  //Checks if a single activity exists for the provided scope/module
	//ie if the scope is required
	public function checkScope($module,$actionType="ACCESS") {
    	$actionType=strtoupper($actionType);
		
			$moduleArr=explode(".",$module);
			if(count($moduleArr)>1) {
				$module=$moduleArr[0];
				array_shift($moduleArr);
				$activity=strtoupper(implode(".",$moduleArr));
			} else {
      		$activity="*";
    	}

    	return $this->checkRole($module,$activity,$actionType);
	}

	function preloadRoleModelData() {
		// println("RoleModel - preloadRoleModelData");

		$privilegehash = RoleModel::getPrivilegeHash();
		$privilegeArr = RoleModel::getRoleList();

		$finalPrivilegeList = [$privilegehash];
		foreach($privilegeArr as $row) {
			if(!in_array($row['role_name'], $finalPrivilegeList)) {
				$finalPrivilegeList[] = $row['role_name'];
			}
		}

		$sql=_db(true)->_selectQ($this->roleTable,"*")
			      ->_where([
		      			"site"=>SITENAME,
		      			'guid'=>$_SESSION['SESS_GUID'],
		      			"privilegehash"=>[$finalPrivilegeList, "IN"]
		      		])->_orderby("allow desc");
			      //->_groupBy("allow");

	  $roleData=$sql->_GET();
	  // echo $roleData;exit();
	  //"category"=>$actionType,"module"=>$module,"activity"=>$activity

	  $finalRoleData = [];
	  $finalRoleStr = [];
	  foreach($roleData as $row) {
	  	$row['module'] = strtolower($row['module']);
	  	$row['activity'] = strtolower($row['activity']);
	  	$row['category'] = strtolower($row['category']);

	  	if($row['activity']=="*") $row['activity'] = "main";

	  	if(!isset($finalRoleData[$row['module']])) $finalRoleData[$row['module']] = [];
	  	if(!isset($finalRoleData[$row['module']][$row['activity']])) $finalRoleData[$row['module']][$row['activity']] = [];
	  	
	  	if(!isset($finalRoleData[$row['module']][$row['activity']][$row['category']]) ||
	  		$finalRoleData[$row['module']][$row['activity']][$row['category']]!="true") {
	  		
	  		$finalRoleData[$row['module']][$row['activity']][$row['category']] = $row['allow'];
	  	}

	  	if(!isset($finalRoleStr[strtolower("{$row['module']}.{$row['activity']}.{$row['category']}")]) ||
	  			$finalRoleStr[strtolower("{$row['module']}.{$row['activity']}.{$row['category']}")]!="true") {

	  		$finalRoleStr[strtolower("{$row['module']}.{$row['activity']}.{$row['category']}")] = $row['allow'];
		  	if($row['category']=="access") {
		  		$finalRoleStr[strtolower("{$row['module']}.{$row['activity']}")] = $row['allow'];
		  	}
	  	}
	  }

	  $_SESSION["ROLEMODEL2"] = [
	  	//"TREE"=>$finalRoleData,
	  	"POLICIES"=>$finalRoleStr
	  ];

	  // printArray($roleData);
	  // printArray([
	  // 	$finalRoleData,
	  // 	$finalRoleStr
	  // ]);
	  return true;
	}
  
  //Check privilege specific task. This is the main function for all validation purpose
  //Uses poolicy string to process requests
	public function checkRole($module,$activity,$actionType="ACCESS", $defaultValue = "false") {
		// println("XXXX->$module,$activity,$actionType");

		if(!isset($_SESSION["ROLEMODEL2"])) {
			$this->preloadRoleModelData();
		}

		$moduleOriginal=$module;

		$module=$this->getScopeModule($module);
		$module=explode(".",$module);
		if(count($module)>1) {
			$activity=str_replace("{$moduleOriginal}.{$moduleOriginal}.","{$moduleOriginal}.",implode(".",array_slice($module,1)).".{$activity}");
			$module=$module[0];
		} else {
			$module=implode(".",$module);
		}
  
    $module = trim(preg_replace("/\%[a-zA-Z0-9]{2}/","",$module));
    
    $activity = $this->cleanActivity($module, $activity);
    
    $actionType=strtoupper($actionType);
    
    $policyStr=[$module,$activity,$actionType];
		$policyStr=implode(".",$policyStr);
    $policyStr = $this->cleanScopePolicy($policyStr);
    $policyStr = strtolower($policyStr);

    $roleParams = explode(".", $policyStr);
		if(count($roleParams)>3) {
    			$roleParams[1] = implode("_", array_slice($roleParams,1,count($roleParams)-2));
			$roleParams[2] = $roleParams[count($roleParams)-1];
			$roleParams = array_slice($roleParams, 0, 3);
    			$policyStr = "{$roleParams[0]}.{$roleParams[1]}.{$roleParams[2]}";
	    	} elseif(count($roleParams)<2) {
			$roleParams[1] = "main";
			$roleParams[2] = "access";
			$policyStr = "{$policyStr}.main.access";
		} elseif(count($roleParams)<3) {
			$roleParams[2] = "access";
			$policyStr = "{$policyStr}.access";
		}

    //println("XXXX->$module,$activity,$actionType->$policyStr");

    if(!defined("SERVICE_ROOT") &&  (getConfig("APPS_STATUS")=="dev" || getConfig("APPS_STATUS")=="development")) {
      _log("RoleUser : {$module}/{$moduleOriginal}-{$activity} :: {$policyStr}","console");
    }

    //Check Globals
    $globalStatus=RoleModel::checkGlobals($policyStr);
		//var_dump($globalStatus);
  	if($globalStatus>0) {
  	  return true;
  	}

  	//printArray($_SESSION["ROLEMODEL2"]);
  	if(isset($_SESSION["ROLEMODEL2"]['POLICIES'][$policyStr])) {
  		return ($_SESSION["ROLEMODEL2"]['POLICIES'][$policyStr]=="true");
  	} else {
  		$this->registerRole($roleParams[0], $roleParams[1], $roleParams[2], SITENAME, false, $_SESSION['SESS_GUID'], $defaultValue);
  		$_SESSION["ROLEMODEL2"]['POLICIES'][$policyStr] = $defaultValue;
  		return ($defaultValue=="true");
  	}
	}

	//Check privilege specific task. This is the main function for all validation purpose
	public function checkRole_old($module,$activity,$actionType="ACCESS") {
    	$moduleOriginal=$module;

			$module=$this->getScopeModule($module);
			$module=explode(".",$module);
			if(count($module)>1) {
				$activity=str_replace("{$moduleOriginal}.{$moduleOriginal}.","{$moduleOriginal}.",implode(".",array_slice($module,1)).".{$activity}");
				$module=$module[0];
			} else {
				$module=implode(".",$module);
			}
    
	    $module = trim(preg_replace("/\%[a-zA-Z0-9]{2}/","",$module));
	    
	    $activity = $this->cleanActivity($module, $activity);
	    
	    $actionType=strtoupper($actionType);
	    
	    $policyStr=[$module,$activity,$actionType];
			$policyStr=implode(".",$policyStr);
	    $policyStr = $this->cleanScopePolicy($policyStr);

	    if(!defined("SERVICE_ROOT") &&  (getConfig("APPS_STATUS")=="dev" || getConfig("APPS_STATUS")=="development")) {
	      _log("RoleUser : {$module}/{$moduleOriginal}-{$activity} :: {$policyStr}","console");
	      //staff/staff.main-MAIN.BANK_DETAILS :: staff.bank_details.create
	    }
    
  		$globalStatus=RoleModel::checkGlobals($policyStr);
// 		var_dump($globalStatus);
    	if($globalStatus>0) {
    	  return true;
    	}
    	
    	$privilegehash=RoleModel::getPrivilegeHash();
    	$roleHash=md5(strtoupper($policyStr.$privilegehash));
      	//printArray([$policyStr,$activity,$roleHash,$privilegehash]);
      
	    if($activity=="*") {
			  if(isset($_SESSION["ROLESCOPE"][$roleHash])) {
			    return $_SESSION["ROLESCOPE"][$roleHash];
			  }

			  $_SESSION["ROLESCOPE"][$roleHash]=0;

			  $privilegehash=RoleModel::getPrivilegeHash();
			  $sql=_db(true)->_selectQ($this->roleTable,"allow,count(*) as cnt")
			      ->_where(array("site"=>SITENAME,"category"=>$actionType,"module"=>$module,"privilegehash"=>$privilegehash))
			      ->_where(['guid'=>$_SESSION['SESS_GUID']])
			      ->_groupBy("allow");

			  $d=$sql->_GET();
			  if($d) {
			    if($d[0]['cnt']>1) {
			      $_SESSION["ROLESCOPE"][$roleHash]=true;
			    } elseif($d[0]['cnt']==1) {
			      if($d[0]['allow']=="true") {
			        $_SESSION["ROLESCOPE"][$roleHash]=true;
			      } else {
			        $_SESSION["ROLESCOPE"][$roleHash]=false;
			      }
			    } else {
			      RoleModel::registerRole($module,"MAIN");
			      $_SESSION["ROLEMODEL"][$roleHash]=(getConfig("ROLEMODEL_DEFAULT_DENY")?false:true);
			    }
			  } else {
			    RoleModel::registerRole($module,"MAIN");
			    $_SESSION["ROLEMODEL"][$roleHash]=(getConfig("ROLEMODEL_DEFAULT_DENY")?false:true);
			  }
			  return $_SESSION["ROLESCOPE"][$roleHash];
	    } else {
		      if(isset($_SESSION["ROLEMODEL"][$roleHash])) {
		        return $_SESSION["ROLEMODEL"][$roleHash];
		      }

      	  $sql=_db(true)->_selectQ($this->roleTable,"id,allow,count(*) as cnt")
		          ->_where(array("site"=>SITENAME,"category"=>$actionType,"module"=>$module,"activity"=>$activity,"privilegehash"=>$privilegehash))
		          ->_where(['guid'=>$_SESSION['SESS_GUID']])
		          ->_orderby("id desc");

		      $data=$sql->_GET();
		      if($data && count($data)>0) {
		        $data=$data[count($data)-1];
		        if($data['cnt']==0) {
		          RoleModel::registerRole($module,$activity,$actionType);
		          $_SESSION["ROLEMODEL"][$roleHash]=(getConfig("ROLEMODEL_DEFAULT_DENY")?false:true);
		          return (getConfig("ROLEMODEL_DEFAULT_DENY")?false:true);
		        } else {
		          $_SESSION["ROLEMODEL"][$roleHash]=($data['allow']=="true")?true:false;
		          return $_SESSION["ROLEMODEL"][$roleHash];
		        }
		      } else {
            RoleModel::registerRole($module,$activity,$actionType);
            $_SESSION["ROLEMODEL"][$roleHash]=(getConfig("ROLEMODEL_DEFAULT_DENY")?false:true);
            return (getConfig("ROLEMODEL_DEFAULT_DENY")?false:true);
          }
	    }
	    
	    return (!getConfig("ROLEMODEL_DEFAULT_DENY"));
  	}
  
  	//Gets the super scope for the site
	protected function getGlobalScopes() {
    	if(!isset($_SESSION["ROLESGLOBAL"][SITENAME])) {
			$this->loadScopeRules();
		}
		return $_SESSION["ROLESGLOBAL"][SITENAME];
	}
	
	//Load all scoperules into Session for users, modules etc by default
	protected function loadScopeRules() {
		$_SESSION["ROLESGLOBAL"][SITENAME]=[];
		$_SESSION["ROLESCOPEMAP"][SITENAME]=[];
		
		$scopeFiles=[
					ROOT.CFG_FOLDER."masters/roles.json"=>"GLOBALS",
					ROOT.CFG_FOLDER."jsonConfig/roles.json"=>"GLOBALS",
					APPROOT.APPS_CONFIG_FOLDER."roles.json"=>"SITE",
				];
		
		$scopeArr=[];$ROLEscopeMap=[];
		foreach($scopeFiles as $scf=>$typ) {
			if(file_exists($scf)) {
				try {
					$scArr=json_decode(file_get_contents($scf),true);
					if(is_array($scArr)) {
						if(!isset($scArr['GLOBALS'])) $scArr['GLOBALS']=[];
						if(!isset($scArr[SITENAME])) $scArr[SITENAME]=[];
						
						if(isset($scArr["ROLESCOPEMAP"])) {
							$ROLEscopeMap=array_merge($ROLEscopeMap,$scArr["ROLESCOPEMAP"]);
						}

						$scArr=array_merge($scArr['GLOBALS'],$scArr[SITENAME]);

						$scopeArr=array_merge($scopeArr,$scArr);
					}
				} catch(Exception $e) {

				}
			}
		}
		$_SESSION["ROLESCOPEMAP"][SITENAME]=$ROLEscopeMap;
		$_SESSION["ROLESGLOBAL"][SITENAME]=$scopeArr;
	}
  
  //Generate and Registers Roles wise permissions
	public function registerRole($module, $activity = "MAIN", $actionType = "ACCESS", $site = SITENAME,$userid = false, $guid = false, $defaultValue = -1) {
			
			if($defaultValue==-1) $defaultValue = getConfig("ROLEMODEL_DEFAULT_DENY");

			if(!in_array($defaultValue, ["true", "false"])) $defaultValue = "false";

	    if($activity==null || strlen($activity)<=0) $activity="MAIN";

	    $activity = $this->cleanActivity($module, $activity);
	    
	    $actionType=strtoupper($actionType);
	    
	    $policyStr=[$module,$activity,$actionType];
	    $policyStr=implode(".",$policyStr);
	    $policyStr = $this->cleanScopePolicy($policyStr);
	    //printArray([$module,$activity,$actionType,$policyStr]);
	    
	    if(!$userid) $userid=$_SESSION['SESS_USER_ID'];

	    if(!$guid) {
	      if(isset($_SESSION['SESS_GUID'])) {
	        $guid=$_SESSION['SESS_GUID'];
	      } else {
	        $guid="global";
	      }
	    }

		$finalData=[];

		$sql=_db(true)->_selectQ(_dbTable("privileges",true),"id,name")
							->_whereOR("site",[$site,"*"]);
		$r=_dbQuery($sql,true);
		if($r) {
			$data=_dbData($r,true);
			_dbFree($r,true);
			foreach($data as $d) {
				if($d['id']<=ROLE_PRIME) continue;

				$pid=$d['id'];
				$privilegehash=md5($d['id'].$d['name']);
				$privilegename=$d['name'];

				$sql=_db(true)->_selectQ($this->roleTable,"count(*) as cnt")
					->_where(array("site"=>$site,"category"=>$actionType,"module"=>$module,"activity"=>$activity,"privilegehash"=>$privilegehash))
					->_where(['guid'=>$guid]);
				$r=_dbQuery($sql,true);
				if($r) {
					$data=_dbData($r);
					if($data[0]['cnt']==0) {
						$finalData[]=[
								//"id"=>"",
								"guid"=>$guid,
								"site"=>$site,
								"category"=>$actionType,
								"module"=>$module,
								"activity"=>$activity,
								"privilegehash"=>$privilegehash,
								"allow"=>$defaultValue,
								"remarks"=>$privilegename,
								"created_by"=>$userid,
								"edited_by"=>$userid
							];
						if($this->version=="2.0") {
							$finalData[count($finalData)-1]['policystr'] = strtolower($policyStr);
							$finalData[count($finalData)-1]['rolehash'] = md5($policyStr.$privilegehash.$site.$guid);
						}
					}
				} else {
					$finalData[]=[
							//"id"=>"",
							"guid"=>$guid,
							"site"=>$site,
							"category"=>$actionType,
							"module"=>$module,
							"activity"=>$activity,
							"privilegehash"=>$privilegehash,
							"allow"=>$defaultValue,
							"remarks"=>$privilegename,
							"created_by"=>$userid,
							"edited_by"=>$userid
						];
					if($this->version=="2.0") {
						$finalData[count($finalData)-1]['policystr'] = strtolower($policyStr);
						$finalData[count($finalData)-1]['rolehash'] = md5($policyStr.$privilegehash.$site.$guid);
					}
				}
			}
		}

		$tables = _db(true)->get_tableList();

		if(in_array(_dbTable("roles",true), $tables)) {
			$sql=_db(true)->_selectQ(_dbTable("roles",true),"id,name")
							->_whereOR("site",[$site,"*"]);
			$r=$sql->_GET();

			if($r) {
				foreach($r as $d) {
					$x1=_db(true)->_selectQ($this->roleTable,"count(*) as cnt")
						->_where(array("site"=>$site,"category"=>$actionType,"module"=>$module,"activity"=>$activity,"privilegehash"=>$d['name']))
						->_where(['guid'=>$guid])
						->_GET();

					if($x1[0]['cnt']===0) {
						$finalData[]=[
								//"id"=>"",
								"guid"=>$guid,
								"site"=>$site,
								"category"=>$actionType,
								"module"=>$module,
								"activity"=>$activity,
								"privilegehash"=>$d['name'],
								"allow"=>$defaultValue,
								"remarks"=>$d['name'],
								"created_by"=>$userid,
								"edited_by"=>$userid
							];
						if($this->version=="2.0") {
							$finalData[count($finalData)-1]['policystr'] = strtolower($policyStr);
							$finalData[count($finalData)-1]['rolehash'] = md5($policyStr.$d['name'].$site.$guid);
						}
					} else {
						$finalData[]=[
							//"id"=>"",
							"guid"=>$guid,
							"site"=>$site,
							"category"=>$actionType,
							"module"=>$module,
							"activity"=>$activity,
							"privilegehash"=>$d['name'],
							"allow"=>$defaultValue,
							"remarks"=>$d['name'],
							"created_by"=>$userid,
							"edited_by"=>$userid
						];
						if($this->version=="2.0") {
							$finalData[count($finalData)-1]['policystr'] = strtolower($policyStr);
							$finalData[count($finalData)-1]['rolehash'] = md5($policyStr.$d['name'].$site.$guid);
						}
					}
				}
			}
		}

		$finalData[]=[
					//"id"=>"",
					"guid"=>$guid,
					"site"=>$site,
					"category"=>$actionType,
					"module"=>$module,
					"activity"=>$activity,
					"privilegehash"=>md5("0guest"),
					"allow"=>$defaultValue,
					"remarks"=>"guest",
					"created_by"=>$userid,
					"edited_by"=>$userid
				];
		if($this->version=="2.0") {
				$finalData[count($finalData)-1]['policystr'] = $policyStr;
				$finalData[count($finalData)-1]['rolehash'] = md5($policyStr.md5("0guest").$site.$guid);
		}

		// printArray($finalData);exit();

		$a = _db(true)->_insert_batchQ($this->roleTable,$finalData)->_run();
		return $a;
	}
  
  	//Clears all the cache in session
	public static function cleanRoleModelCache() {
    if(isset($_SESSION["ROLEMODEL_VERSION"])) {
      unset($_SESSION["ROLEMODEL_VERSION"]);
    }
		if(isset($_SESSION["ROLEMODEL"])) {
			unset($_SESSION["ROLEMODEL"]);
		}
		if(isset($_SESSION["ROLEMODEL2"])) {
			unset($_SESSION["ROLEMODEL2"]);
		}
		if(isset($_SESSION["ROLESCOPE"])) {
			unset($_SESSION["ROLESCOPE"]);
		}
		if(isset($_SESSION["ROLESGLOBAL"])) {
			unset($_SESSION["ROLESGLOBAL"]);
		}
		if(isset($_SESSION["ROLESCOPEMAP"])) {
			unset($_SESSION["ROLESCOPEMAP"]);
		}
	}
	
	//Generates and returns the Required Privilege Hash for the Current Loggedin User
	public static function getPrivilegeHash() {
		if(!isset($_SESSION["SESS_PRIVILEGE_HASH"])) {
			if(isset($_SESSION["SESS_PRIVILEGE_NAME"]) && isset($_SESSION["SESS_PRIVILEGE_ID"])) {
				$_SESSION["SESS_PRIVILEGE_HASH"]=md5($_SESSION["SESS_PRIVILEGE_ID"].$_SESSION["SESS_PRIVILEGE_NAME"]);
			} else {
				$_SESSION["SESS_PRIVILEGE_HASH"]=md5("0guest");
			}
		}
		//trigger_logikserror("Role access error");
		return $_SESSION["SESS_PRIVILEGE_HASH"];
	}

	public static function getRoleList() {
		if(isset($_SESSION["SESS_ROLE_LIST"])) {
			return $_SESSION["SESS_ROLE_LIST"];
		} else {
			return [];
		}
	}
  
  	//Gets the scope name for the given module
	protected function getScopeModule($module) {
		if(!isset($_SESSION["ROLESCOPEMAP"][SITENAME])) {
			RoleModel::loadScopeRules();
		}
		if(isset($_SESSION["ROLESCOPEMAP"][SITENAME][$module])) {
			return $_SESSION["ROLESCOPEMAP"][SITENAME][$module];
		}
		return $module;
	}
  
  	//Clear the policy string
  	protected function cleanScopePolicy($policyStr) {
	    if(!$policyStr || strlen($policyStr)<=0) return "";
	    
	    // $policyStr=str_replace(".MAIN","",$policyStr);
	    $policyStr=str_replace(".main-MAIN",".MAIN",$policyStr);
			$policyStr=str_replace(".MASTER","",$policyStr);
			$policyStr=str_replace(".INDEX","",$policyStr);
			$policyStr=str_replace(".CORE","",$policyStr);
			$policyStr=str_replace(".ACCESS","",$policyStr);
	// 		$policyStr=str_replace(".PAGES","",$policyStr);
	    
	    $policyStr = preg_replace("/\.\./",".",$policyStr);
	    $policyStr = preg_replace("/\%[a-zA-Z0-9]{2}/","",$policyStr);
	    
	    $policyStr=str_replace("*","MAIN",$policyStr);
	    
	    return ($policyStr);//strtolower
  	}
  
  	//Generate Activity String
	protected function cleanActivity($module, $activity = null) {
	    if($activity==null || strlen($activity)<=0) $activity="MAIN";
	    
	    $module = str_replace("modules/", "", str_replace("popup/", "", $module));
	    $module = str_replace("/", "_", $module);
	    
	    //$activity=preg_replace("/{$module}\./","",$activity);
		$activity=preg_replace("/".strtolower($module)."\./","",strtolower($activity));
		//str_replace(strtolower($module),"",strtolower($activity))
	    $activity = preg_replace("/\%[a-zA-Z0-9]{2}/","",$activity);
	    
	    return strtoupper($activity);
	}
}
?>
