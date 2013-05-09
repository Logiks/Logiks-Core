<?php
if(!defined('ROOT')) exit('No direct script access allowed');
//checkServiceSession();

if(strlen(getConfig("MAIL_ENGINE"))<=0) {
	LoadConfigFile(ROOT . "config/mail.cfg","CONFIG");
}
$f2=APPROOT.getConfig("APPS_CONFIG_FOLDER")."mail.cfg";
if(file_exists($f2)) {
	LoadConfigFile($f2,"CONFIG");
}

loadHelpers("email");

if(defined("APPS_CACHE_FOLDER")) $destination_path = APPROOT . APPS_CACHE_FOLDER . "mails/";
else  $destination_path = ROOT . CACHE_FOLDER . "mails/";

$maxFileSize="4000000";
$attach=null;
$target_path = "";
$fileSize=-1;

if(!file_exists($destination_path)) {
	mkdir($destination_path,0777,true);
	chmod($destination_path,0777);
}
if(!file_exists($destination_path)) {
	//echo "Cache Path Not Found. Can't Send Attachments<br/>";
}

if(isset($_POST['mailto'])) $to=$_POST['mailto']; else $to=getConfig("default_to");
if(isset($_POST['subject'])) $subject=$_POST['subject']; else $subject="Logiks Mail Service";
if(isset($_POST['from'])) $from=$_POST['from']; else $from="";
if(isset($_POST['cc'])) $cc=$_POST['cc']; else $cc="";
if(isset($_POST['bcc'])) $bcc=$_POST['bcc']; else $bcc="";
if(isset($_POST['title'])) $title=$_POST['title']; else $title="Logiks Mail";
if(isset($_POST['footer'])) $footer=$_POST['footer']; else $footer="";
if(isset($_POST['onsuccess'])) $onsuccess=$_POST['onsuccess']; else $onsuccess="Mail Successfully Sent.";
if(isset($_POST['onerror'])) $onerror=$_POST['onerror']; else $onerror="Error Occured While Sending Mail!";

if(isset($_POST['body'])) $body=$_POST['body']; else $body=EMail::createMailBodyForArray($_POST,$title,$footer);

if(strlen($to)<=0)  {
	echo("<div width=100% align=center style='color:red'><p>No Recieptants</p></div>");
	exit();
}

if(isset($_FILES['attach']) && $_FILES['attach']['size']>0) {
	$fileName = $_FILES['attach']['name'];
	$tmpName  = $_FILES['attach']['tmp_name'];
	$ext = substr(strrchr($fileName, "."), 1);
	$randName = md5(rand() * time());
	$target_path .= $destination_path . $randName . '.' . $ext;
	$fileSize = $_FILES['attach']['size'];
	
	if($fileSize>-1) {
		if($fileSize<$maxFileSize) {
			if (!move_uploaded_file($tmpName,$target_path)) {
				echo "<div width=100% align=center style='color:red'><p>Error Occured During Uploading. Try Again.</p></div>";
			} else {
				$attach=$target_path;
			}
		} else {
			echo "<div width=100% align=center style='color:red'><p>Too Large File Being Attached.</p></div>";
		}
	}
}

ob_start();
echo "<style>";
echo file_get_contents(SiteLocation."misc/themes/default/dataexport.css");
echo "</style>";
echo $body;
$data=ob_get_contents();
ob_clean();
//echo "Sending Mail";

$a=false;
if(getConfig("MAIL_ENGINE")=="simple" && $attach==null) {
	$a=sendMail($to,$subject,$data,$from,$cc,$bcc);
} else {//Pear::Mail
	$email=new EMail();
	$a=$email->sendMimeMessageAdvanced($to,$subject,$cc,$bcc,$data,$attach);
}

if($a==true) {
	echo("<div width=100% align=center><p>$onsuccess</p></div>");
} else {
	echo("<div width=100% align=center style='color:red'><p>$onerror</p></div>");
}
if(file_exists($target_path)) unlink($target_path);
?>