<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("printPageToolbar")) {
	function printPageToolbar($btns,$toolButtons) {
		$cnt=1;
		if(is_array($btns)) {
			foreach($btns as $a=>$b) {
				if(isset($b['bar'])) {
					echo $b['bar'];
				} elseif(isset($b['label'])) {
					if(!isset($b['id'])) $b['id']="toolbtn_$cnt";
					if(!isset($b['onclick'])) $b['onclick']="";
					if(!isset($b['tips'])) $b['tips']=$b['label'];
					$cnt++;
					echo "<div id='{$b['id']}' class='label' onClick=\"{$b['onclick']}\" title='{$b['tips']}'>{$b['label']}</div>";
				} else {
					if(!isset($b['id'])) $b['id']="toolbtn_$cnt";
					if(!isset($b['title'])) $b['title']="";
					if(!isset($b['icon'])) $b['icon']="";
					if(!isset($b['tips'])) $b['tips']=$b['title'];
					if(!isset($b['onclick'])) $b['onclick']="";
					$cnt++;
					if($toolButtons)
						echo "<button id='{$b['id']}' onClick=\"{$b['onclick']}\" title='{$b['tips']}'><div class='{$b['icon']}'>{$b['title']}</div></button>";
					else
						echo "<a class='button' id='{$b['id']}' onClick=\"{$b['onclick']}\" title='{$b['tips']}'>{$b['title']}</a>";
					//<a class='{$b['icon']}'></a>
				}
			}
		} elseif(function_exists($btns)) {
			call_user_func($btns);
		} else {
			echo $btns;
		}
	}
	function getPageComponentClass($key) {
		if(isset($_SESSION["page_classes"][$key])) {
			return $_SESSION["page_classes"][$key];
		} else return $key;
	}
}
?>
