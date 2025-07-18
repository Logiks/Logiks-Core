<?php
if(!defined('ROOT')) exit('No direct script access allowed');

//Major support from http://avatars.io/.

$avlMethods=array("facebook","gravatar","logiks","photoid","twitter","instagram","email");//,"googleplus"

if(isset($_REQUEST['avatar'])) {
	$avtr=explode("::", $_REQUEST['avatar']);
	$_REQUEST['authorid']=$avtr[1];
	$_REQUEST['method']=$avtr[0];
} elseif(isset($_REQUEST['authorid'])) {
} elseif(isset($_REQUEST['uri'])) {
	$newMedia = loadMedia($_REQUEST['uri']);
	if($newMedia!=$_REQUEST['uri']) {
		header("Location:$newMedia");
		exit();
	}
} elseif(isset($_SESSION['SESS_USER_ID'])) {
	$_REQUEST['authorid']=$_SESSION['SESS_USER_ID'];
} else {
	printDefaultAvatar();
}
$method="email";
if(isset($_REQUEST['method'])) {
	$method=$_REQUEST['method'];
}
if(!in_array($method,$avlMethods)) {
	printServiceErrorMsg("Method Not Supported");
	//printDefaultAvatar();
	exit();
}
//printArray($_REQUEST);exit($method);

if(isset($_REQUEST['action'])) {
	if($_REQUEST['action']=="src") {
		printServiceMsg($avlMethods);
	} else {
		printAvatarPhoto($method);
	}
} else {
	printAvatarPhoto($method);
}

function printAvatarPhoto($method) {
	if(isset($_REQUEST['authorid']) && strlen($_REQUEST['authorid'])>0){
		$authorid=explode("@",$_REQUEST['authorid']);
		$authorid=$authorid[0];
		if($method=="facebook") {
			//$url="http://graph.facebook.com/{$authorid}/picture?type=large";//?redirect=false
			$url="http://avatars.io/facebook/{$authorid}/large";
			$data=file_get_contents($url);
			printAvatar($data,"jpeg");
		} elseif($method=="gravatar") {
			$url="http://www.gravatar.com/avatar/".md5(strtolower(trim($_REQUEST['authorid'])))."?s=80&d=mm&r=g";
			$data=file_get_contents($url);
			printAvatar($data,"png");
		} elseif($method=="twitter") {
			$url="http://avatars.io/twitter/{$authorid}/large";
			$data=file_get_contents($url);
			printAvatar($data,"jpeg");
		} elseif($method=="instagram") {
			$url="http://avatars.io/instagram/{$authorid}/large";
			$data=file_get_contents($url);
			printAvatar($data,"jpeg");
		} elseif($method=="email") {
			//d=  Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
			//r=  Maximum rating (inclusive) [ g | pg | r | x ]
			$url="http://www.gravatar.com/avatar/";
			$url .= md5( strtolower( trim( $_REQUEST['authorid'] ) ) );
      		$url .= "?s=120&d=identicon&r=g";
			$data=file_get_contents($url);
			printAvatar($data,"png");
			//printDefaultAvatar();
		}
		/*
			$url="https://api.twitter.com/1/users/profile_image?screen_name={$authorid}&size=bigger";
			$data=file_get_contents($url);
			printAvatar($data,"png");
		} elseif($method=="googleplus") {
			$url="https://profiles.google.com/s2/photos/profile/{$authorid}";
			$data=file_get_contents($url);
			echo $data;
		} */
		elseif($method=="logiks") {
			$profilePhoto=APPROOT.APPS_USERDATA_FOLDER."profile_photos/{$_REQUEST['authorid']}";
			if(file_exists($profilePhoto.".png")) {
				header("content-type:image/png");
				readfile($profilePhoto.".png");
			} elseif(file_exists($profilePhoto.".gif")) {
				header("content-type:image/gif");
				readfile($profilePhoto.".gif");
			} elseif(file_exists($profilePhoto.".jpg")) {
				header("content-type:image/jpg");
				readfile($profilePhoto.".jpg");
			} elseif(file_exists($profilePhoto.".jpeg")) {
				header("content-type:image/jpeg");
				readfile($profilePhoto.".jpeg");
			} else {
				printDefaultAvatar();
			}
		} elseif($method=="photoid") {
			if(!isset($_REQUEST['src'])) {
				$_REQUEST['src']=getConfig("DBTABLE_AVATAR");
				if(strlen($_REQUEST['src'])<=0) {
					$_REQUEST['src']=_dbTable("avatar");
				}
			}
			$lx=_service("viewphoto")."&type=view&loc=db&dbtbl={$_REQUEST['src']}&image={$_REQUEST['authorid']}";
			header("Location:$lx");
			exit();
		}
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

				header('Pragma:cache');
				header('Cache-Control: max-age='.(60*60));
				// header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 60)));
				header("content-type:image/$format");
				readfile($f);
			} else {
				printDefaultAvatar();
			}
		} else {
			printDefaultAvatar();
		}
	}
	exit();
}
function printDefaultAvatar() {
	$f=ROOT.loadMedia("images/avatar.png",true);
	if(!file_exists($f)) $f=ROOT.loadMedia("images/user.png",true);
	header('Pragma:cache');
	header('Cache-Control: max-age='.(60*60));
	// header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 60)));
	header("content-type:image/png");
	readfile($f);
	exit();
}
?>
