<?php
/*
 * This file contains the functionality for Data System of Logiks.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("_dataURI")) {
	include_once dirname(__FILE__)."/LogiksData.php";
	
	function _dataURI($srcURI,$type="auto",$reload=false) {
		if(strpos($srcURI, "http://")===0 || strpos($srcURI, "https://")===0 || strpos($srcURI, "ftp://")===0) {
			//Will do the processing
		} else {
			if(!file_exists($srcURI)) $srcURI=APPROOT.$srcURI;
			if(!file_exists($srcURI)) $srcURI=ROOT.$srcURI;
			if(!file_exists($srcURI)) return false;
		}

		$cacheKey="DATA-URI::".md5($srcURI);
		if($reload) {
			$cacheData=false;
		} else {
			$cacheData=_cache($cacheKey);
			if($cacheData==null || count($cacheData)<=0) {
				$cacheData=false;
			}
		}

		if($cacheData===false) {
			if($type==null || $type=="auto") {
				$ext=explode(".", $srcURI);
				$ext=end($ext);
			} else {
				$ext=$type;
			}

			$data=[];
			switch ($ext) {
				case 'csv':
						$data = array_map('str_getcsv', file($srcURI));
					break;
				case 'json':
						$data=json_decode(file_get_contents($srcURI),true);
					break;
				case 'xml':
						$xml = simplexml_load_string(file_get_contents($srcURI), "SimpleXMLElement", LIBXML_NOCDATA);
						$json = json_encode($xml);
						$data = json_decode($json,TRUE);
						if(count($data)==1) {
							$data=$data[array_keys($data)[0]];
						}
					break;
				case 'cfg':
					$data=ConfigFileReader::LoadCfgFile($srcURI);
					break;
				case 'cfg2':
					$data=ConfigFileReader::LoadCfg2File($srcURI);
					break;
				case 'ini':
					$data=ConfigFileReader::LoadIniFile($srcURI);
					break;
				case 'lst':
					$data=ConfigFileReader::LoadListFile($srcURI);
					break;
				default:
					return false;
					break;
			}

			$cacheData=$data;
			_cache($cacheKey,$cacheData);
		}
		
		return new LogiksData($cacheData,$cacheKey);
	}

	function _dataSQL($sqlObj,$reload=true) {
		if(!is_a($sqlObj,"AbstractQueryBuilder")) {
			trigger_logikserror("_dataSQL only accepts AbstractQueryBuilder objects.");
			return false;
		}
		$cacheKey="DATA-SQL::".md5($sqlObj->_SQL());
		if($reload) {
			$cacheData=$sqlObj->_get();
			_cache($cacheKey,$cacheData);
		} else {
			$cacheData=_cache($cacheKey);
			if($cacheData==null || count($cacheData)<=0) {
				$cacheData=$sqlObj->_get();
				_cache($cacheKey,$cacheData);
			}
		}

		return new LogiksData($cacheData,$cacheKey);
	}
}
?>
