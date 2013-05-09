<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$avlMethods=array("facebook","twitter","gravatar");//,"googleplus"
$method="facebook";
if(isset($_REQUEST['method'])) {
	$method=$_REQUEST['method'];
}

if(isset($_REQUEST['action'])) {
	if($_REQUEST['action']=="src") {
		printServiceMsg($avlMethods);
	} else {
		printAvatarPhoto($method,$avlMethods);
	}
} else {
	printAvatarPhoto($method,$avlMethods);
}

function printAvatarPhoto($method,$avlMethods) {
	if(isset($_REQUEST['authorid']) && strlen($_REQUEST['authorid'])>0 && in_array($method,$avlMethods)){
		$authorid=explode("@",$_REQUEST['authorid']);
		$authorid=$authorid[0];

		if($method=="facebook") {
			$url="http://graph.facebook.com/{$authorid}/picture?type=large";//?redirect=false
			$data=file_get_contents($url);
			printAvatar($data,"jpeg");
		} elseif($method=="twitter") {
			$url="https://api.twitter.com/1/users/profile_image?screen_name={$authorid}&size=bigger";
			$data=file_get_contents($url);
			printAvatar($data,"png");
		} elseif($method=="gravatar") {
			$url="http://www.gravatar.com/avatar/".md5(strtolower(trim($_REQUEST['authorid'])))."?s=80&d=mm&r=g";
			$data=file_get_contents($url);
			printAvatar($data,"png");
		}
		/*elseif($method=="googleplus") {
			$url="https://profiles.google.com/s2/photos/profile/{$authorid}";
			$data=file_get_contents($url);
			echo $data;
		} */
	} else {
		printDefaultAvatar();
	}
}

function printAvatar($data,$format,$default="") {
	if(strlen($data)>10) {
		header("content-type:image/$format");
		echo $data;
	} else {
		if(strlen($default)>0) {
			$f=loadMedia($default);
			if(file_exists($f)) {
				$format=explode($default);
				$format=$format[count($format)-1];
				header("content-type:image/$format");
				readfile($f);
			} else {
				printDefaultAvatar();
			}
		} else {
			printDefaultAvatar();
		}
	}
}
function printDefaultAvatar() {
	$f=loadMedia("images/avatar.png");
	if(!file_exists($f)) $f=loadMedia("images/user.png");
	header("content-type:image/png");
	readfile($f);
}
?>
