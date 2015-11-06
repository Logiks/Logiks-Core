<?php
/*
 * HTMLAssets is introduced to replace JSPHP and CSSPHP and centraling the html assets
 * controls that being CSS and JS along with LogiksTheme Engine.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 24/01/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class HTMLAssets {

	protected $themeEngine=null;
	protected $siteName=null;
	protected $cachePeriod=24;

	private $loadedAssets=array();
	private $assetBuffer=array();

	public function __construct($themeEngine,$site=null) {
		if($site==null) $site=SITENAME;
		$this->themeEngine=$themeEngine;
		$this->siteName=$site;
	}

	protected function findAsset($assetName,$assetType="css",$params=array()) {
		$theme=$this->themeEngine->getTheme();

		$basePath=APPS_FOLDER.$this->siteName."/";

		$cacheLink="{$assetName}-{$assetType}-{$theme}";
		$cachePath=_metaCache("HTMLAssets:".strtoupper($assetType),$cacheLink);
		if(!$cachePath) {
			$searchPath=array();
			if(!isset($this->assetBuffer["{$assetType}-{$theme}"])) {
				$moduleFolders=getPluginFolders("modules",$this->siteName);
				$searchPath[]=$basePath."{$assetType}/";
				$searchPath[]=THEME_FOLDER."{$theme}/";
				foreach ($moduleFolders as $key => $value) {
					$value=str_replace("#ROOT#", "", $value);
					$value=str_replace("#APPROOT#", $basePath, $value);
					$value=str_replace("#SITENAME#", $this->siteName, $value);
					$value=str_replace("#THEME#", THEME_FOLDER.$theme, $value);

					$searchPath[]=$value;
				}
				$this->assetBuffer["{$assetType}-{$theme}"]=$searchPath;
			} else {
				$searchPath=$this->assetBuffer["{$assetType}-{$theme}"];
			}
			$searchPath[]=THEME_FOLDER."default/";
			//printArray($searchPath);

			$finalPath="";
			switch ($assetType) {
				case 'css':
					$searchPath[]=THEME_FOLDER."default/";
					foreach ($searchPath as $fs) {
						if(basename($fs)!="modules") {
							if(file_exists(ROOT.$fs."{$assetName}.css")) {
								$finalPath=$fs."{$assetName}.css";
								break;
							}
						}
						if(file_exists(ROOT.$fs."{$assetName}/style.css")) {
							$finalPath=$fs."{$assetName}/style.css";
							break;
						}
					}
				break;
				case 'js':
					// $searchPath[]=API_FOLDER."js/";
					$searchPath[]=THEME_FOLDER."default/js/";
					$assetDir=explode(".", $assetName);
					if(count($assetDir)>1) $assetDir=$assetDir[0];
					else $assetDir=$assetName;

					foreach ($searchPath as $fs) {
						if(basename($fs)!="modules") {
							if(file_exists(ROOT.$fs."{$assetName}.js")) {
								$finalPath=$fs."{$assetName}.js";
								break;
							} elseif(strlen($assetDir)>0 && file_exists(ROOT.$fs."{$assetDir}/{$assetName}.js")) {
								$finalPath=$fs."{$assetDir}/{$assetName}.js";
								break;
							}
						} elseif(file_exists(ROOT.$fs."{$assetName}/script.js")) {
							$finalPath=$fs."{$assetName}/script.js";
							break;
						} elseif(file_exists(ROOT.$fs."{$assetDir}/{$assetName}.js")) {
							$finalPath=ROOT.$fs."{$assetDir}/{$assetName}.js";
							break;
						}
					}
				break;
			}
			if($finalPath!=null && strlen($finalPath)>0) {
				_metaCacheUpdate("HTMLAssets:".strtoupper($assetType),$cacheLink,$finalPath);
			}
		} else {
			$finalPath=$cachePath;
		}
		//printArray($searchPath);echo $finalPath."\n";
		if($finalPath!=null && strlen($finalPath)>0) {
			return $finalPath;
		} else {
			return false;
		}
	}

	public function getAsset($assetName,$assetType="css",$params=array()) {
		if($assetType==null || strlen($assetType)<=0) return false;
		$path=$this->getCDNResource($assetName,$assetType);
		if($path) return array(
				"FILE"=>"CDN",
				"LINK"=>$path,
			);
		$path=$this->findAsset($assetName,$assetType,$params);
		if($path) {
			return array(
					"FILE"=>ROOT.$path,
					"LINK"=>SiteLocation.$path
				);
		}
		return false;
	}

	public function getAssetPath($assetName,$assetType="css",$params=array()) {
		$src=$this->getAsset($assetName,$assetType);

		if($src) {
			if($src['FILE']=="CDN") return $src['LINK'];
			else return $src['FILE'];
		}
		return false;
	}

	public function getAssetURL($assetName,$assetType="css",$params=array()) {
		$src=$this->getAsset($assetName,$assetType);

		if($src) return $src['LINK'];
		return false;
	}

	public function printAsset($assetName,$assetType="css",$params=array()) {
		if($assetType==null || strlen($assetType)<=0) return  false;

		$path=$this->getAssetPath($assetName,$assetType,$params);

		if(substr($path, 0,7)=="http://" || substr($path, 0,8)=="https://" || substr($path, 0,6)=="ftp://") {
			echo "/*************Start***$assetName****************/\n";
			echo _cachePrint($path);
			echo "\n/*************END***$assetName****************/\n\n";
		} else {
			echo "/*************Start***$assetName****************/\n";
			readfile($path);
			echo "\n/*************END***$assetName****************/\n\n";
		}
	}
	// public function printAssetTag($assetName,$assetType="css",$params=array()) {
	// 	if($assetType==null || strlen($assetType)<=0) return  false;
	// }
	// public function printAssetCompressed($assetName,$assetType="css",$params=array()) {
	// 	if($assetType==null || strlen($assetType)<=0) return  false;
	// }

	//CDN Function
	public function getCDNResource($resource,$type) {
		if(getConfig("CDN_DISABLE")) {
			//unset($_SESSION['CDN-RESOURCES']);
			return false;
		}
		$jsondb=SimpleJSONDB::getInstance();
		$cdnSrc=$jsondb->getItem("cdn",$type);

		if(is_array($cdnSrc) && isset($cdnSrc[$resource])) {
			return $cdnSrc[$resource];
		}
		return false;
	}

	//Other Internal Functions
	public function reset($assetType) {
		if(isset($this->loadedAssets[$assetType])) {
			$this->loadedAssets[$assetType]=array();
		}
	}
	public function debugPrint($assetType) {
		if(isset($this->loadedAssets[$assetType])) {
			var_dump($this->loadedAssets[$assetType]);
		}
	}

}
?>
