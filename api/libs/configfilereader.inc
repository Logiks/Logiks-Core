<?php
/*
 * Confiuration File Type (cfg,dat,lst,ini,xml,json) reader
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class ConfigFileReader {
	public static function supportedFileTypes() {
		return array(
				"cfg"=>"Configuration Files",
				"lst"=>"Data List Files",
				"ini"=>"INI Files",
				"xml"=>"XML Files",
				"json"=>"JSON Files",
			);
	}
	public static function LoadFile($path) {
		if(file_exists($path) && is_readable($path)) {
			$pArr=pathinfo($path);
			$ext=strtolower($pArr["extension"]);
			if($ext=="cfg") return ConfigFileReader::LoadCfgFile($path);
			elseif($ext=="cfg2") return ConfigFileReader::LoadCfg2File($path);
			elseif($ext=="ini" || $ext=="map") return ConfigFileReader::LoadIniFile($path);
			elseif($ext=="lst") return ConfigFileReader::LoadListFile($path);
			elseif($ext=="xml") return ConfigFileReader::LoadXmlFile($path);
			elseif($ext=="json") return ConfigFileReader::LoadJsonFile($path);
			else return false;
		}
		return false;
	}
	public static function LoadListFile($path) {
		$arr=array();
		if(file_exists($path) && is_readable($path)) {
			$cfgData=file_get_contents($path);
			$cfgData=explode("\n",$cfgData."\n");
			foreach($cfgData as $s) {
				if(strlen($s)<=1) continue;
				if(substr($s,0,2)=="//") continue;
				if(substr($s,0,1)=="#") continue;
				
				if(strlen($s)>0) {
					array_push($arr,$s);
				}
			}
		} else {
			if(MASTER_DEBUG_MODE=="true") echo "<br/>List File Could Not Be Loaded " . $path;
		}
		return $arr;
	}
	public static function LoadIniFile($path) {
		$arr=array();
		if(file_exists($path) && is_readable($path)) {
			$cfgData=file_get_contents($path);
			$cfgData=explode("\n",$cfgData."\n");
			foreach($cfgData as $s) {
				if(strlen($s)<=1) continue;
				if(substr($s,0,2)=="//") continue;
				if(substr($s,0,1)=="#") continue;
				
				if(strlen($s)>0) {
					$ss=explode("=",$s);
					$s1=$ss[0];
					unset($ss[0]);
					$s2=implode("=",$ss);
					$arr[$s1]=$s2;
				}
			}
		} else {
			if(MASTER_DEBUG_MODE=="true") echo "<br/>INI File Could Not Be Loaded " . $path;
		}
		return $arr;
	}
	public static function LoadCfgFile($path) {
		$arr=array();
		if(file_exists($path) && is_readable($path)) {
			$cfgData=file_get_contents($path);
			$cfgData=explode("\n",$cfgData."\n");
			$mode="CONFIG";
			foreach($cfgData as $s) {
				if(strlen($s)<=1) continue;
				if(substr($s,0,2)=="//") continue;
				if(substr($s,0,1)=="#") continue;
				
				if(strlen($s)>0) {
					$ss=explode("=",$s);
					if(count($ss)<=1) {
						$mode=substr($s,1,strlen($s)-2);
					} else {
						$s1=$ss[0];
						unset($ss[0]);
						$s2=implode("=",$ss);
						if(strlen($mode)>0) {
							$arr[$mode][$s1]=$s2;
						} else {
							$arr[$s1]=$s2;
						}
					}
				}
			}
		} else {
			if(MASTER_DEBUG_MODE=="true") echo "<br/>CFG File Could Not Be Loaded " . $path;
		}
		return $arr;
	}
	public static function LoadCfg2File($path) {
		$arr=array();
		if(file_exists($path) && is_readable($path)) {
			$cfgData=file_get_contents($path);
			$cfgData=explode("\n",$cfgData."\n");
			foreach($cfgData as $s) {
				if(strlen($s)<=1) continue;
				if(substr($s,0,2)=="//") continue;
				if(substr($s,0,1)=="#") continue;
				if(substr($s,0,1)=="[") continue;

				if(strlen($s)>0) {
					$ss=explode("=",$s);
					
					$temp = &$arr;
					foreach(explode('_', $ss[0]) as $key) {
				        $temp = &$temp[$key];
				    }
				    $temp = array_reverse($ss);
				}
			}
		} else {
			if(MASTER_DEBUG_MODE=="true") echo "<br/>CFG File Could Not Be Loaded " . $path;
		}
		//printArray($arr);
		return $arr;
	}
	public static function LoadXmlFile($path) {
		$arr=array();
		if(file_exists($path) && is_readable($path)) {
			$xml=simplexml_load_file($path);
			foreach($xml->children() as $child){
				$arr1=$child->attributes();
				$s1="";
				$s2="";
				if(isset($arr1["name"])) $s1="".$arr1["name"];
				if(isset($arr1["value"])) $s2="".$arr1["value"];
				else $s2="".$child;
				$arr[$s1]=$s2;
			}
		} else {
			if(MASTER_DEBUG_MODE=="true") echo "<br/>XML File Could Not Be Loaded " . $path;
		}
		return $arr;
	}
	public static function LoadJsonFile($path) {
		$arr=array();
		if(file_exists($path) && is_readable($path)) {
			$data=file_get_contents($path);
			$data=str_replace("`","\"",$data);
			$arr=(array)json_decode($data);
		} else {
			if(MASTER_DEBUG_MODE=="true") echo "<br/>JSON File Could Not Be Loaded " . $path;
		}
		return $arr;
	}
	
	public static function SaveListFile($path,$arr) {
		$s="";
		foreach($arr as $a) {
			$s.="{$a}\n";
		}
		return file_put_contents($path,$s);
	}
	public static function SaveIniFile($path,$arr) {
		$s="";
		foreach($arr as $m=>$n) {
			$s.="{$m}={$n}\n";
		}
		return file_put_contents($path,$s);
	}
	public static function SaveCfgFile($path,$arr) {
		$s="";
		foreach($arr as $a=>$b) {
			$s.="[{$a}]\n";
			foreach($b as $m=>$n) {
				$s.="{$m}={$n}\n";
			}
		}
		return file_put_contents($path,$s);
	}
}
?>
