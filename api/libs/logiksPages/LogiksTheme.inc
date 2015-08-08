<?php
/*
 * LogiksTheme is central to the new theme engine that comes now with Logiks v3.6.6+
 * LogiksTheme will encompass the older skining and theming abilities along with enhancing
 * the UX experiance.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 24/02/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class LogiksTheme {

	protected $theme="";
	protected $themeFolder="";
	protected $themeURL="";

	protected $templateDir=array();

	protected $htmlAssets=null;

	public function __construct($theme,$site=null) {
		$this->theme=$theme;
		$this->themeFolder=ROOT.THEME_FOLDER.$theme."/";
		$this->themeURL=ROOTURL.THEME_FOLDER.$theme."/";

		if(!is_dir($this->themeFolder)) {
			trigger_error("Required Theme $theme Not Found",E_USER_ERROR);
		}

		$this->htmlAssets=new HTMLAssets($this,$site);

		$this->templateDir=array(
				ROOT.APPS_FOLDER.$site."/pages/viewpage/",
				ROOT.APPS_FOLDER.$site."/css/templates/",
				$this->themeFolder."templates/",
			);
	}

	public function getAssetHandler() {
		return $this->htmlAssets;
	}
	public function getTheme() {
		return $this->theme;
	}
	public function getThemeInfo() {
		$info=array(
			"name"=>$this->theme,
			"path"=>$this->themeFolder,
			"url"=>$this->themeURL,
			"photo"=>"screenshot.png",
		);
		if(file_exists($this->themeFolder.$info['photo'])) {
			$info['photo']=$this->themeURL.$info['photo'];
		}
		$abtTheme="{$this->themeFolder}theme.txt";
		$themeData=LogiksConfig::fetchConfigFile($abtTheme);

		$info=array_merge($themeData,$info);

		return $info;
	}

	public function findTemplate($template) {
		foreach ($this->templateDir as $tmpl) {
			if(file_exists($tmpl.$template)) return $tmpl.$template;
		}
		return false;
	}

	public function findCSS($css) {
		return $this->htmlAssets->getAssetURL($css,"css");
	}

	public function findJS($js) {
		return $this->htmlAssets->getAssetURL($js,"js");
	}
}
?>
