<?php
/*
 * Mobility Device Identifier and other mobility related functions
 * 
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("getUserDeviceType")) {
	function getUserDeviceType() {
		$out=array("pc","mobile","tablet");		
		if(isset($_COOKIE['USER_DEVICE'])) {
			return $_COOKIE['USER_DEVICE'];
		} else {
			$detect = new DeviceDetection();
			$device=$out[0];
			if($detect->isMobile()) {
				$device=$out[1];
			} elseif($detect->isTablet()) {
				$device=$out[2];
			} else {
				$device=$out[0];
			}
			setCookie("USER_DEVICE",$device,null,"/");
			return $device;
		}
	}
	
	function switchUserDeviceType($device="pc") {
		$device=strtolower($device);
		if($device=="pc") {
			setCookie("USER_DEVICE",$device,null,"/");
		} elseif($device=="mobile") {
			setCookie("USER_DEVICE",$device,null,"/");
		} elseif($device=="tablet") {
			setCookie("USER_DEVICE",$device,null,"/");
		} else {
			setCookie("USER_DEVICE","pc",null,"/");
		}
		return $device;
	}
	
	function getUserDevice() {
		$detect = new DeviceDetection();
		if($detect->isBlackberrytablet()){//$detect->isMobile()
			return "BlackBerry Tablet";
		}elseif($detect->isAndroidtablet()){//$detect->isMobile()
			return "Android Tablet";
		}elseif($detect->isWindowsphone()){//$detect->isMobile()
			return "Windows Phone";
		}elseif($detect->isWindows()){//$detect->isMobile()
			return "Windows Mobile";
		}elseif($detect->isIpad()){//$detect->isMobile()
			return "iPad";
		}elseif($detect->isIphone()){//$detect->isMobile()
			return "iPhone";
		}elseif($detect->isBlackberry()){
			return "BlackBerry Mobile";
		}elseif($detect->isAndroid()){//$detect->isMobile()
			return "Android Mobile";
		}elseif($detect->isPalm()){//$detect->isMobile()
			return "Palm";
		}elseif($detect->isGeneric()){//$detect->isMobile()
			return "Generic";
		}else{
			return "PC";
		}
	}
}

class DeviceDetection {
	protected $accept;
	protected $userAgent;
	protected $isMobile = false;
	protected $isTablet = false;
		
	protected $isAndroid = null;
	protected $isAndroidtablet = null;
	protected $isBlackberry = null;
	protected $isBlackberrytablet = null;
	protected $isIphone = null;
	protected $isIpad = null;	
	protected $isPalm = null;
	protected $isWindows = null;
	protected $isWindowsphone = null;
	protected $isGeneric = null;
	
	protected $isOpera = null;	
	
	protected $devices = array(
		"android" => "android.*mobile",
		"androidtablet" => "android(?!.*mobile)",
		"blackberry" => "blackberry",
		"blackberrytablet" => "rim tablet os",
		"iphone" => "(iphone|ipod)",
		"ipad" => "(ipad)",
		"palm" => "(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)",
		"windows" => "windows ce; (iemobile|ppc|smartphone)",
		"windowsphone" => "(windows phone os|windows|microsoft)",
		"generic" => "(kindle|mobile|mmp|midp|o2|pda|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap|opera mini)"
	);
	
	public function __construct() {
		if(!_server('HTTP_USER_AGENT')) {
			_envData("SERVER",'HTTP_USER_AGENT',"Mozilla/5.0 (X11; Linux i686) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.168 Safari/535.19");
		}
		$this->userAgent = _server('HTTP_USER_AGENT');
		//$this->userAgent = "(iPhone; U; CPU iPhone OS 4_3_2 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML  like Gecko) Version/5.0.2 Mobile/8H7 Safari/6533.18.5,CGI/1.1,HTTP/1.0,GET,219.91.184.34";	
		
		if(_server('HTTP_ACCEPT')) $this->accept = _server('HTTP_ACCEPT');
		else $this->accept="text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
		
		if (_server('HTTP_X_WAP_PROFILE') || _server('HTTP_PROFILE')) {
			$this->isMobile = true;
		} elseif (strpos($this->accept, 'text/vnd.wap.wml') > 0 || strpos($this->accept, 'application/vnd.wap.xhtml+xml') > 0) {
			$this->isMobile = true;
		} else {
			foreach ($this->devices as $device => $regexp) {
				if ($this->isDevice($device)) {
					$this->isMobile = true;
				}
			}
		}		
		
		if($this->isBlackberrytablet()){
			$this->isTablet=true;
			$this->isMobile = false;
		}elseif($this->isAndroidtablet()){
			$this->isTablet=true;
			$this->isMobile = false;
		}elseif($this->isIpad()){
			$this->isTablet=true;
			$this->isMobile = false;
		}
	}
	
	/**
	 * Overloads isAndroid() | isAndroidtablet() | isIphone() | isIpad() | isBlackberry() | isBlackberrytablet() | isPalm() | isWindowsphone() | isWindows() | isGeneric() through isDevice()
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return bool
	 */
	public function __call($name, $arguments) {
		$device = substr($name, 2);
		if ($name == "is" . ucfirst($device) && array_key_exists(strtolower($device), $this->devices)) {
			return $this->isDevice($device);
		} else {
			trigger_logikserror("Method $name not defined", E_WARNING);
		}
	}

	/**
	 * Returns true if any type of mobile device detected, including special ones
	 * @return bool
	 */
	public function isMobile() {
		return $this->isMobile;
	}
	
	/**
	 * Returns true if any type of Tablet device detected, including special ones
	 * @return bool
	 */
	public function isTablet() {
		return $this->isTablet;
	}
	
	/**
	 * Returns true if any type of Generic device detected, including special ones
	 * @return bool
	 */
	public function isDevice($device) {
		$var = "is" . ucfirst($device);
		$return = $this->$var === null ? (bool) preg_match("/" . $this->devices[strtolower($device)] . "/i", $this->userAgent) : $this->$var;
		if ($device != 'generic' && $return == true) {
			$this->isGeneric = false;
		}

		return $return;
	}
}
?>
