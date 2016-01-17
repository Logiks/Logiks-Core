<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');
//All functions and resources to be used by service system

if(!function_exists("getServiceCMD")) {
	function getServiceCMD() {
		$scmd=$_REQUEST['scmd'];
		return $scmd;
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
}
?>
