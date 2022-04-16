<?php
/*
 * Contains core routing logic and connected functions
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 4.5
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('autoModuleRoute')) {

	//To use just call this from module's index.php - autoModuleRoute(__DIR__);
	function autoModuleRoute($modulePath, $params = [], $allowListing = false, $baseURL = "modules") {
        $slug = _slug("a/subpage/c/d");
        //printArray($slug);
        
        $file0 = $modulePath."/pages/main.php";
        $apiFile = $modulePath."/api.php";
        $routeFile = $modulePath."/route.php";
        $routeJSONFile = $modulePath."/route.json";

        $moduleName = basename($modulePath);
        
        if(file_exists($apiFile)) include_once $apiFile;

        if(file_exists($routeFile)) {
        	if($allowListing && strlen($slug['subpage'])<=0) {
        		$dir = scandir($modulePath."/pages/");
                $dir = array_splice($dir,2);
                
                echo "<ul class='list-group' style='width: 800px;margin:auto;margin-top:50px;'>";
                foreach($dir as $a=>$b) {
                    $t = toTitle(str_replace("_", " ", str_replace(".php", "", $b)));
                    $l = _link("{$baseURL}/{$moduleName}/".str_replace(".php", "", $b));
                    println("<li class='list-group-item'><a href='{$l}'>{$t}</a></li>");
                }
                echo "</ul>";
        	} else {
				include_once $routeFile;
        	}
        } elseif(file_exists($routeJSONFile)) {
        	if($allowListing && strlen($slug['subpage'])<=0) {
        		$dir = scandir($modulePath."/pages/");
                $dir = array_splice($dir,2);
                
                echo "<ul class='list-group' style='width: 800px;margin:auto;margin-top:50px;'>";
                foreach($dir as $a=>$b) {
                    $t = toTitle(str_replace("_", " ", str_replace(".php", "", $b)));
                    $l = _link("{$baseURL}/{$moduleName}/".str_replace(".php", "", $b));
                    println("<li class='list-group-item'><a href='{$l}'>{$t}</a></li>");
                }
                echo "</ul>";
        	} else {
        		$jsonRouteData = json_decode(file_get_contents($routeJSONFile), true);
	        	if($jsonRouteData && isset($jsonRouteData['path'])) {
	        		if(isset($jsonRouteData['path'][$slug['subpage']])) {
	        			if(is_string($jsonRouteData['path'][$slug['subpage']])) {
	        				include_once $modulePath."/pages/{$jsonRouteData['path'][$slug['subpage']]}";
	        			} elseif(isset($jsonRouteData['path'][$slug['subpage']]['src'])) {
	        				include_once $modulePath."/pages/{$jsonRouteData['path'][$slug['subpage']]['src']}";
	        			} else {
	        				echo "<h1 align=center><br><br>Requested Route Path Is Corrupted</h1>";
	        			}
	        		} else {
	        			echo "<h1 align=center><br><br>Requested Route Not Found</h1>";
	        		}
	        	} else {
	        		echo "<h1 align=center><br><br>Route file seems to be corrupted</h1>";
	        	}
        	}
        } else {
        	if(strlen($slug['subpage'])<=0) {
	            if(file_exists($file0)) {
	                include_once $file0;
	            } elseif($allowListing) {
	                $dir = scandir($modulePath."/pages/");
	                $dir = array_splice($dir,2);
	                
	                echo "<ul class='list-group' style='width: 800px;margin:auto;margin-top:50px;'>";
	                foreach($dir as $a=>$b) {
	                    $t = toTitle(str_replace("_", " ", str_replace(".php", "", $b)));
	                    $l = _link("{$baseURL}/{$moduleName}/".str_replace(".php", "", $b));
	                    println("<li class='list-group-item'><a href='{$l}'>{$t}</a></li>");
	                }
	                echo "</ul>";
	            } else {
	                echo "<h1 align=center><br><br>Error opening Module</h1>";
	            }
	        } else {
	            $file = $modulePath."/pages/{$slug['subpage']}.php";
	            if(file_exists($file)) {
	                include_once $file;
	            } elseif(file_exists($file0)) {
	                include_once $file0;
	            } elseif($allowListing) {
	                echo "<h1 align=center><br><br>Requested Page Not Found</h1>";
	                //header("Location:"._link("{$baseURL}/".basename($modulePath)));
	            } else {
	                echo "<h1 align=center><br><br>Error opening Module</h1>";
	            }
	        }
        }
    }
}
?>