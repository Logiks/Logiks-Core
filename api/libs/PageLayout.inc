<?php
/*
 * This Class For Developers To Create MultiLayout Systems Where Users Will Be Able To Change The Page Contents.
 * All this works only for Generated Pages. Designed Pages Do Not Neccessarily Use This System..
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 24/02/2012
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class PageLayout {
	private $layoutTemplateDir=array();

	private $layout="";
	private $layoutDir="";

	private $default_Layout_Params=array(
			"preheader"=>"",
			//{wrapper}
				"header"=>"",
				"prebanner"=>"",
				"banner"=>"",
				"breadcrumb"=>"",
				//{contentspace}
					"precontent"=>"",
					"sidebar_left"=>"",
					//{content}
						"content"=>"",
					//{content}
					"sidebar_right"=>"",
					"postcontent"=>"",
				//{contentspace}
				"prefooter"=>"",
			//{wrapper}
			"footer"=>"",
			"copyright"=>"",
			"postfooter"=>"",
			
			"topbar"=>"",
			"bottombar"=>"",
			"leftbar"=>"",
			"rightbar"=>"",
			
			"sidebar"=>"",//Used In WorkSpace Layout
		);

	private $html5Containers=array(
			"article","aside","canvas",
			"footer","header","mark","nav","section",
		);

	public function __construct() {
		if(defined("APPS_TEMPLATE_LAYOUT_FOLDER")) {
			if(!in_array(BASEPATH . APPS_TEMPLATE_LAYOUT_FOLDER,$this->layoutTemplateDir) && file_exists(ROOT.BASEPATH . APPS_TEMPLATE_LAYOUT_FOLDER)) {
				array_push($this->layoutTemplateDir,BASEPATH . APPS_TEMPLATE_LAYOUT_FOLDER);
			}
		}
		if(defined("APPS_THEME")) {
			if(!in_array(THEME_FOLDER.APPS_THEME . "/layouts/",$this->layoutTemplateDir) && file_exists(ROOT.THEME_FOLDER.APPS_THEME . "/layouts/")) {
				array_push($this->layoutTemplateDir,THEME_FOLDER.APPS_THEME . "/layouts/");
			}
		}
		if(!in_array(TEMPLATE_LAYOUT_FOLDER,$this->layoutTemplateDir) && file_exists(ROOT.TEMPLATE_LAYOUT_FOLDER)) {
			array_push($this->layoutTemplateDir,TEMPLATE_LAYOUT_FOLDER);
		}
	}
	public function loadLayoutTemplate($layoutTemplate) {
		foreach($this->layoutTemplateDir as $a) {
			$f=ROOT . $a . $layoutTemplate . ".tpl";
			if(file_exists($f)) {
				$this->layout=$layoutTemplate;
				$this->layoutDir=$a;
				$this->printScripts();
				return true;
			}
		}
		return false;
	}
	public function printLayout($params) {
		if(!is_array($params) || count($params)<=0) {
			dispErrMessage("Layout Configuration Array MissConfigured.","Layout Config Error",409,'apps');
			exit();
		}

		//Load The Layout Template
		$fl=ROOT . $this->layoutDir . $this->layout . ".tpl";
		$bdir="";
		if(defined("APPROOT")) $bdir=APPROOT; else $bdir=ROOT;
		if(defined("APPS_PAGES_FOLDER")) $bdir.=APPS_PAGES_FOLDER; else $bdir.=PAGES_FOLDER;

		foreach($this->default_Layout_Params as $pkey=>$plnk) {
			if(array_key_exists($pkey,$params)) {
				$ft=$bdir.trim($params[$pkey]);
				$fInfo=pathinfo($ft);

				if($fInfo["extension"]=="php") {
					if(file_exists($ft)) {
						$s="";
						ob_start();
						include $ft;
						$s=ob_get_contents();
						ob_clean();
						if(in_array($pkey, $this->html5Containers)) {
							$params[$pkey]="<$pkey id='{$pkey}'>{$s}</$pkey>";
						} else {
							$params[$pkey]="<div id='{$pkey}'>{$s}</div>";
						}
					} else {
						$params[$pkey]="";
					}
				} elseif($fInfo["extension"]=="tpl" && function_exists("_template")) {
					$s="";
					if(file_exists($ft)) {
						$s=_template($ft);
					} else {
						$s=_template($params[$pkey]);
					}
					if(in_array($key, $this->html5Containers)) {
						$params[$pkey]="<$pkey id='{$pkey}'>{$s}</$pkey>";
					} else {
						$params[$pkey]="<div id='{$pkey}'>{$s}</div>";
					}
				} else {
					$params[$pkey]="";
				}
			}
		}
		$templates=new TemplateEngine();
		$templates->loadTemplate($fl);

		ob_start();
		$templates->display($params);
		$body=ob_get_contents();
		ob_clean();

		$body=TemplateEngine::processTemplate($body,$params,false);
		return $body;
	}
	public function printScripts() {
		$f1=ROOT . $this->layoutDir . $this->layout . ".css";
		$f2=ROOT . $this->layoutDir . $this->layout . "-ie6.css";
		$f3=ROOT . $this->layoutDir . $this->layout . ".js";

		$n=_css(array("layout",$this->layout));
		if($n<=0) {
			if(file_exists($f1)) {
				$x=SiteLocation . str_replace(ROOT,"",$f1);
				echo "<link href='$x' rel='stylesheet' type='text/css' media='all' />\n";
			}
		}
		if(file_exists($f2)) {
			$x=SiteLocation . str_replace(ROOT,"",$f2);
			echo "<!--[if IE 6]>";
			echo "<link href='$x' rel='stylesheet' type='text/css' media='all' />\n";
			echo "<![endif]--> ";
		}
		if(file_exists($f3)) {
			$x=SiteLocation . str_replace(ROOT,"",$f3);
			echo "<script src='$x' type='text/javascript' language='javascript'></script> ";
		}
	}
	public static function readLayoutConfig($cfgFile) {
		$out=array();
		if(file_exists($cfgFile)) {
			$data=file_get_contents($cfgFile);
			$data=explode("\n",$data);
			foreach($data as $a=>$b) {
				if(strlen($b)>0 && strpos($b,"#")!==0)  {
					$c=explode(":",$b);
					if(count($c)==2) $out[$c[0]]=$c[1];
				}
			}
		}
		return $out;
	}
	public function getDefaultLayoutParams() {
		return $this->default_Layout_Params;
	}
}
 ?>
