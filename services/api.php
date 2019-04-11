<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');
//All functions and resources to be used by service system

if(!function_exists("getServiceCMD")) {
	function getServiceCMD() {
		$scmd=$_REQUEST['scmd'];
		return $scmd;
	}

	//Handle Action Method Invocations
	function handleActionMethodCalls($defaultData = []) {
	    if(strlen($_REQUEST['action'])<=0) {
	      printServiceMsg($defaultData);
	      return false;
	    }

	    $rmiMethod = generateActionMethodName($_REQUEST['action']);
	    
	    if($rmiMethod && function_exists($rmiMethod)) {
	      $return = call_user_func($rmiMethod);
	      if($return!==null) {
	        printServiceMsg($return);
	      }
	    } else {
	      printServiceErrorMsg(501,"Method Not Found for ".clean($_REQUEST['action']));
	    }
	}
	  
	//Generates a name for Remote Method Function, returns false if proposedMethodName is null or if rmi method function allready exists
	function generateActionMethodName($proposedMethodName) {
	    if($proposedMethodName==null || strlen($proposedMethodName)<=0) return false;
	    
	    $rmiFunc = str_replace(' ', '-', $proposedMethodName); // Replaces all spaces with hyphens.
	    $rmiFunc = preg_replace('/[^A-Za-z0-9\-_.]/', '', $rmiFunc); // Removes special chars.
	    $rmiFunc =  preg_replace('/-+/', '-', $rmiFunc);
	    $rmiFunc = strtolower($rmiFunc);
	    
	    $rmiFunc = "_service_$rmiFunc";
	    
	    return $rmiFunc;
	}

	function isAjax() {
		if(_server('HTTP_REFERER')) {
			$x=_server('HTTP_X_REQUESTED_WITH');
			
			if(isset($_REQUEST['syshash']) && $_REQUEST['syshash']==getSysHash()) {
				return true;
			} elseif($x) {
				$isAjax = $x AND strtolower($x) === 'xmlhttprequest';
				if($isAjax) {
					return true;
				}
			}
		} elseif(isset($_REQUEST['syshash']) && $_REQUEST['syshash']==getSysHash()) {
			return true;
		}
		return false;
	}

	//All Error Printing Funcs
	function passErrorMsg($msg) {
		if(is_array($msg)) {
			$msg=json_encode($msg);

			echo "<script language='javascript' type='text/javascript'>
				console.error('$msg');
			</script><h3>$msg</h3>";
		} else {
			echo "<script language='javascript' type='text/javascript'>
				if(typeof logiksAlert=='undefined') alert('ERROR : $msg');
				else logiksAlert('ERROR : $msg');
				console.error('$msg');
			</script><h3>$msg</h3>";
		}
	}

	//All Error Printing Funcs
	function passWarnMsg($msg) {
		if(is_array($msg)) {
			$msg=json_encode($msg);

			echo "<script language='javascript' type='text/javascript'>
				console.warn('$msg');
			</script><h3>$msg</h3>";
		} else {
			echo "<script language='javascript' type='text/javascript'>
				if(typeof logiksAlert=='undefined') alert('WARNING : $msg');
				else logiksAlert('WARNING : $msg');
				console.warn('$msg');
			</script><h3>$msg</h3>";
		}
	}

	function printServiceErrorMsg($errCode,$errMsg=null,$errorImg="") {
		if($errCode==null) $errCode=500;
		if(is_numeric($errCode)) {
		  $errorMessage=getErrorTitle($errCode);
	    } else {
	    	$errorMessage="";
	    }
	    if($errMsg==null) {
	    	$errMsg=$errorMessage;
	    }
		if($errorImg!=null && strlen($errorImg)>0) {
			$errorImg=loadMedia($errorImg);
		} else {
			$errorImg=loadMedia("images/errors/msg_default.png");
		}

		$arr=array();
		$arr['ErrorCode']=$errCode;
		$arr['Data']=$errMsg;
		$arr['ErrorDescs']=_replace($errorMessage);
		$arr['ErrorIcon']=$errorImg;

		printServiceData($arr,null,$errCode);
	}

	function printServiceMsg($msgData,$msgCode=200,$msgImage="") {
		// if($msgImage!=null && strlen($msgImage)>0) {
		// 	$msgImage=loadMedia($msgImage);
		// }

		$arr=array();
		$arr['MessageCode']=$msgCode;
		$arr['Data']=$msgData;
		//$arr['MessageIcon']=$msgImage;

		printServiceData($arr,null,$msgCode);
	}

	function printServiceData($arrData,$format=null,$statusCode=200) {
		if($statusCode==null || !is_numeric($statusCode)) $statusCode=200;
		$envelop=getMsgEnvelop();

		if($format==null) $format=$_REQUEST['format'];

		ob_clean();

		if(getConfig("SERVICE_SHOW_REQUEST")) {
			$arrData['Request']['uri']=SiteLocation._server('REQUEST_URI');
			$arrData['Request']['site']=$_REQUEST['site'];
			$arrData['Request']['scmd']=$_REQUEST['scmd'];
			$arrData['Request']['format']=$format;
			if(_server("REQUEST_TIME_FLOAT")) {
				$arrData['Request']['latency']=(microtime(true)-_server("REQUEST_TIME_FLOAT"));
			} else {
				$arrData['Request']['latency']=(microtime(true)-_server("REQUEST_SERVICE_START"));
			}

			$arrData['Request']['slug']=array();
			foreach ($_REQUEST['slug'] as $key => $value) {
				$arrData['Request']['slug']["SLUG_{$key}"]=$value;
			}
		}
		$htmlFormats=array("list","select","table","html");
		if(in_array($format, $htmlFormats)) {
			if(isset($_REQUEST['debug']) && $_REQUEST['debug']=="true") {
				header("Content-Type:text/text");
			} else {
				header("Content-Type:text/html");
			}
		} else {
			header("Content-Type:text/{$format}");
		}
		
		if(getConfig("SERVICE_SHOW_ERROR_CODE")) {
			header("Status: $statusCode");
			//header(':', true, $statusCode);
			header("HTTP/1.1 $statusCode");
		}

		$msgData=$arrData['Data'];
		// $msgData=array(
		// 		// "a"=>"m",
		// 		// "c"=>"n",
				
		// 		// "a","b",

		// 		// "a"=>array("x"=>array("m"=>"n"),"z"=>"w"),
		// 		// "b"=>array("m"=>"n","o"=>"p"),

		// 		// array("x"=>array("m"=>"n"),"z"=>"w"),
		// 		// array("m"=>"n","o"=>"p"),
		// 	);
		switch ($format) {
			case 'table':
				if(is_array($msgData)) {
					if(isset($_REQUEST['autoformat']) && $_REQUEST['autoformat']=="false") {
						printFormattedArray($msgData,false,"table");
					} else {
						printFormattedArray($msgData,true,"table");
					}
				} else {
					echo "<tr><td>$msgData</td></tr>";
				}
				break;

			case 'list':
				if(is_array($msgData)) {
					if(isset($_REQUEST['autoformat']) && $_REQUEST['autoformat']=="false") {
						printFormattedArray($msgData,false,"list");
					} else {
						printFormattedArray($msgData,true,"list");
					}
				} else {
					echo "<li>$msgData</li>";
				}
				break;
			
			case 'select':
				if(is_array($msgData)) {
					if(isset($_REQUEST['autoformat']) && $_REQUEST['autoformat']=="false") {
						printFormattedArray($msgData,false,"select");
					} else {
						printFormattedArray($msgData,true,"select");
					}
				} else {
					echo "<option>$msgData</option>";
				}
				break;

			case 'xml':
				$xml=new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\" ?><service></service>");
				arrayToXML($arrData,$xml);
				//array_walk_recursive($arrData, array ($xml, 'addChild'));
				echo $xml->asXML();
				break;

			case 'json':
				echo json_encode($arrData);
				break;

			case 'txt':
				if(is_array($msgData)) {
					trigger_logikserror(900, E_USER_ERROR);
				} else {
					$msgData=strip_tags($msgData);
					echo $msgData;
				}
				break;
			case "event":
			case "event-stream":
				if(is_array($msgData)) {
					echo "data: ";
					printFormattedArray($msgData);
				} else {
					$msgData=strip_tags($msgData);
					echo "data: $msgData";
				}
				flush();
				break;
			default://Anything else (raw,css,js)
				if(is_array($msgData)) {
					printFormattedArray($msgData);
				} else {
					$msgData=strip_tags($msgData);
					echo $msgData;
				}
				break;
		}
	}
	
	function checkServiceSession($checkScope=true,$redirect=true) {
		//$ls=new LogiksSecurity();
		//$ls->checkUserSiteAccess($_REQUEST['forsite'],true);
		
		//trigger_logikserror("Accessing Forbidden Page",E_USER_ERROR,401);
		
		if(session_check(false,false)) {
			if(!$checkScope) {
				return true;
			}
			if(checkUserScope($_REQUEST["scmd"])) {
				$acp=$_SESSION['SESS_ACCESS_SITES'];
				if(!in_array(SITENAME,$acp)) {
					printServiceErrorMsg(403, "{$_REQUEST['forsite']} is not available for you");
					if($redirect) exit();
					else return false;
				}
				return true;
			} else {
				printServiceErrorMsg(403, "Service Access Forbidden");
				if($redirect) exit();
				else return false;
			}
		} else {
			printServiceErrorMsg(403, "Service Access Forbidden");
			if($redirect) exit();
			else return false;
		}
	}
}
?>
