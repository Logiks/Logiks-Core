<?php
if(!defined('ROOT')) exit('No direct script access allowed');

include "Mail.php";
@include "Mail/mime.php";

//$headers["Reply-To"] = "reply@address.com"; 
//$headers["Content-Type"] = "text/plain; charset=ISO-2022-JP"; 
//$headers["Return-path"] = "returnpath@address.com"; 

if(!isset($GLOBALS['CONFIG']["smtp_username"])) {
	LoadConfigFile(ROOT . "config/mail.cfg","CONFIG");
}

if (!function_exists('validEmail')) {
	function isValidEmail($address) {
		return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address)) ? FALSE : TRUE;
	}
	function sendMail($to,$subject,$body,$cc="",$bcc="") {
		$from=$GLOBALS['CONFIG']["smtp_username"];
		
		if(isset($_SESSION["SESS_USER_EMAIL"]) && strlen($_SESSION["SESS_USER_EMAIL"])>0) {
			$from=$GLOBALS['CONFIG']["smtp_username"];
		} else {
			$from=$GLOBALS['CONFIG']["smtp_username"];
		}
		
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "To: $to" . "\r\n";
		$headers .= "From: $from" . "\r\n";
		$headers .= "Reply-To: $from" . "\r\n";
		$headers .= "Cc: $cc" . "\r\n";
		$headers .= "Bcc: $bcc" . "\r\n";
		
		return mail($to,$subject,$body,$headers);
	}
}
class EMail {
	
	protected static $keywords= array("mailto","subject","from","cc","bcc","title","footer","body","attach");
	protected $smtpinfo=null;
	
	public function __construct() {
		$this->smtpinfo=$this->createDefaultSMTPInfo();		
	}
	
	protected function createDefaultSMTPInfo() {
		$smtp=array();
		
		$host="";
		if(strlen($GLOBALS['CONFIG']["smtp_encryption"])>0) {
			$host=strtolower($GLOBALS['CONFIG']["smtp_encryption"])."://";
		}
		$host.=$GLOBALS['CONFIG']["smtp_host"];
		
		$smtp["host"] = $host;
		$smtp["port"] = $GLOBALS['CONFIG']["smtp_port"];
		$smtp["auth"] = (boolean)$GLOBALS['CONFIG']["smtp_auth"];
		$smtp["username"] = $GLOBALS['CONFIG']["smtp_username"];
		$smtp["password"] = $GLOBALS['CONFIG']["smtp_password"];
		$smtp["cc"] = $GLOBALS['CONFIG']["default_cc"];
		$smtp["bcc"] = $GLOBALS['CONFIG']["default_bcc"];

		return $smtp;
	}
	
	public function getSMTPInfo() {
		return $this->smtpinfo;
	}
	
	public function updateSMTPInfo($smtpData) {
		if(is_array($smtpData)) {
			foreach(array_keys($smtpData) as $a) {
				$this->smtpinfo[$a]=$smtpData[$a];
			}
		}
	}
	protected function addKeyword($a) {
		if(!in_array($a)) {
			$this->keywords[sizeof($this->keywords)]=$a;
		}
	}
	
	protected function removeKeyword($a) {
		if(in_array($a)) {
			unset($this->keywords[$a]);
		}
	}
	
	function sendPlainMessage($recipients, $subject, $cc, $bcc, $msgBody) {		
		if($this->smtpinfo==null) {
			$this->smtpinfo=$this->createDefaultSMTPInfo();
		}

		$headers["From"]    = $this->smtpinfo["username"];
		$headers["To"]      = $recipients;
		$headers["Subject"] = $subject;
		$headers["cc"] = $this->smtpinfo["cc"].",".$cc;
		$headers["bcc"] = $this->smtpinfo["bcc"].",".$bcc;
		
		$from=$this->createFrom();
		if(strlen($from)>0) $headers["From"]=$from;
		
		$mail_object =@Mail::factory("smtp", $this->smtpinfo);
		$mail_object->send($recipients, $headers, $msgBody);

		if (PEAR::isError($mail_object)) {
		   echo("<p>" . $mail_object->getMessage() . "</p>");
		   return false;
		} else {
		   return true;
		}
	}
	function sendHTMLMessage($recipients, $subject, $cc, $bcc, $msgBody, $allowPlain=true) {
		return $this->sendMimeMessageAdvanced($recipients, $subject, $cc, $bcc, $msgBody, null,$allowPlain);
	}
	
	function sendMimeMessage($recipients, $subject, $msgBody, $file,$allowPlain=true) {
		return $this->sendMimeMessageAdvanced($recipients, $subject, "", "", $msgBody, $file,$allowPlain);
	}
	
	function sendMimeMessageAdvanced($recipients, $subject, $cc, $bcc, $msgBody, $file, $allowPlain=true) {
		if($this->smtpinfo==null) {
			$this->smtpinfo=$this->createDefaultSMTPInfo();
		}
		
		$hdrs = array(
				  'From'    => $this->smtpinfo["username"],
				  'To'    => $recipients,
				  'Subject' => $subject,
				  'Cc' => $this->smtpinfo["cc"].",".$cc,
				  'Bcc' => $this->smtpinfo["bcc"].",".$bcc,
				  );
		
		$from=$this->createFrom();
		if(strlen($from)>0) $hdrs["From"]=$from;
		
		$mime = new Mail_Mime("\n");
		
		$mime->setHTMLBody($msgBody);
		if($allowPlain) $mime->setTXTBody($this->processHTMLToText($body));

		if($file!=null) {
			include_once ROOT."config/mimes.php";
			if(is_array($file)) {
				for($i=0;$i<sizeof($file);$i++) {
					$mime->addAttachment($file[$i], getMimeTypeForFile($file[$i]));
				}
			} else {
				$mime->addAttachment($file, getMimeTypeForFile($file[$i]));
			}
		}

		$body = $mime->get();
		$hdrs = $mime->headers($hdrs);
		
		$mail_object =@Mail::factory("smtp", $this->smtpinfo);
		$mail_object->send($recipients, $hdrs, $body);
		
		if (PEAR::isError($mail_object)) {
		   //echo("<p>" . $mail_object->getMessage() . "</p>");
		   return false;
		} else {
		   //echo("<div width=100% align=center><p>Message successfully sent!</p></div>");
		   return true;
		}
	}
	
	public static function createMailBodyForArray($data,$title='',$footer='') {
		$body="";
		$allfields=array_keys($data);
		$footer='';
		$body.="<div class=mailcontainer>";
		$body.="<table class=mailform align='center' width='750px' border='0' cellpadding='2' cellspacing='0'>";
		$body.="<caption>$title</caption>";
		for($i=0;$i<sizeof($allfields);$i++) {
			if(!in_array($allfields[$i],EMail::$keywords)){
				if($i%2==0)
					$body .= "<tr class='columnName even'><td width=200px align=right>";
				else
					$body .= "<tr class='columnName odd'><td align=right>";

				$body .="<b>" . ucwords($allfields[$i])."</b></td><td width=5px class=columnEqual align=center>".":"."</td><td class='columnInput'>".$_POST[$allfields[$i]]."</td></tr>";
			}
		}
		$body.="</table>";
		$body.="<p class='footer'>$footer</p></div></div>";
		$body.="</div>";
		return $body;
	}
	public function processHTMLToText($document) {
	// $document should contain an HTML document.  This will remove HTML tags, javascript sections
	// and white space. It will also convert some common HTML entities to their text equivalent.

		$search = array ("'<script[^>]*?>.*?</script>'si",  // Strip out javascript
						 "'<[/!]*?[^<>]*?>'si",          // Strip out HTML tags
						 "'([rn])[s]+'",                // Strip out white space
						 "'&(quot|#34);'i",                // Replace HTML entities
						 "'&(amp|#38);'i",
						 "'&(lt|#60);'i",
						 "'&(gt|#62);'i",
						 "'&(nbsp|#160);'i",
						 "'&(iexcl|#161);'i",
						 "'&(cent|#162);'i",
						 "'&(pound|#163);'i",
						 "'&(copy|#169);'i",
						 "'&#(d+);'e");                    // evaluate as php

		$replace = array ("", "", "\1", "\"", "&", "<", ">", " ", chr(161), chr(162),
						 chr(163), chr(169), "chr(\1)");

		$document = preg_replace("<br/>", "\n", $document);
		$document = preg_replace("&nbsp;", " ", $document);
		$text = preg_replace($search, $replace, $document);
		return $text;
	}
	private function createFrom() {
		if(isset($_SESSION["SESS_USER_EMAIL"]) && strlen($_SESSION["SESS_USER_EMAIL"])>0) {
			//return "From: ".$_SESSION["SESS_USER_NAME"]." ".$_SESSION["SESS_USER_EMAIL"]; 
			//return $_SESSION["SESS_USER_EMAIL"];
			return "";
		} else {
			return "";
		}
	}
}
?>
