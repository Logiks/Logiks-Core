<?php
/*
 * All Email related functions
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

//$headers["Reply-To"] = "reply@address.com"; 
//$headers["Content-Type"] = "text/plain; charset=ISO-2022-JP"; 
//$headers["Return-path"] = "returnpath@address.com"; 

if(!function_exists('isValidEmail')) {
	if(is_dir(ROOT.VENDORS_FOLDER."PHPMailer/")) {
		$fs=[
				ROOT.VENDORS_FOLDER."PHPMailer/PHPMailer/phpmailer.inc",
				ROOT.VENDORS_FOLDER."PHPMailer/PHPMailer/smtp.inc",
				ROOT.VENDORS_FOLDER."PHPMailer/PHPMailer/pop3.inc",
			];
		foreach ($fs as $f) {
			if(file_exists($f)) include $f;
		}
	}
	function isValidEmail($address) {
		return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address)) ? FALSE : TRUE;
	}
	
	function sendMail($to,$subject,$body,$params=array(),$appKey="app") {
		$mailConfig=loadJSONConfig("mail",$appKey);

		$defaultParams=array(
				"from"=>"",
				"cc"=>"",
				"bcc"=>"",
				"replyto"=>"",
				"attachments"=>array(),
				"msg_plain"=>strip_tags($body),
			);
		if(_session("SESS_USER_EMAIL") && strlen(_session("SESS_USER_EMAIL"))>0) {
			$defaultParams['from']=_session("SESS_USER_EMAIL");
		} else {
			$defaultParams['from']=$mailConfig["default_from"];
		}
		$defaultParams['replyto']=$defaultParams['from'];

		if(is_array($params)) {
			$params=array_merge($defaultParams,$params);
		}
		
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		//$headers .= "To: $to" . "\r\n";
		if(strlen($params['from'])>0) $headers .= "From: {$params['from']}" . "\r\n";
		if(strlen($params['replyto'])>0) $headers .= "Reply-To: {$params['replyto']}" . "\r\n";
		if(strlen($params['cc'])>0) $headers .= "Cc: {$params['cc']}" . "\r\n";
		if(strlen($params['bcc'])>0) $headers .= "Bcc: {$params['bcc']}" . "\r\n";
		
		return mail($to,$subject,$body,$headers);
	}

	function sendMailSMTP($to,$subject,$msgBody,$params=array(),$appKey="app") {
		$mailConfig=loadJSONConfig("mail",$appKey);

		$defaultParams=array(
				"from"=>"",
				"cc"=>"",
				"bcc"=>"",
				"replyto"=>"",
				"attachments"=>array(),
				"msg_plain"=>strip_tags($msgBody),
			);
		if(_session("SESS_USER_EMAIL") && strlen(_session("SESS_USER_EMAIL"))>0) {
			$defaultParams['from']=_session("SESS_USER_EMAIL");
		} else {
			$defaultParams['from']=$mailConfig["default_from"];
		}
		$defaultParams['replyto']=$defaultParams['from'];

		if(is_array($params)) {
			$params=array_merge($defaultParams,$params);
		}

		$mail = new PHPMailer();
		$mail->isSMTP();

		if(isset($mailConfig['debug']) && $mailConfig['debug']) {
			$mail->SMTPDebug = 3;                               // Enable verbose debug output
			$mail->Debugoutput = 'html';
		}
		//$mailConfig['engine']=="smtp"

		//Set the hostname of the mail server
		$mail->Host = $mailConfig['smtp_host'];
		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$mail->Port = $mailConfig['smtp_port'];
		//Set the encryption system to use - ssl (deprecated) or tls
		$mail->SMTPSecure = $mailConfig['smtp_secure'];
		//Whether to use SMTP authentication
		$mail->SMTPAuth = $mailConfig['smtp_auth'];
		//Username to use for SMTP authentication - use full email address for gmail
		$mail->Username = $mailConfig['smtp_username'];
		//Password to use for SMTP authentication
		$mail->Password = $mailConfig['smtp_password'];

		if(strlen($params['from'])>0) $mail->setFrom($params['from']);

		if(is_array($to)) {
			foreach($to as $t) $mail->addAddress($t);
		} else {
			$mail->addAddress($to);
		}

		$mail->Subject = $subject;
		$mail->msgHTML($msgBody);
		$mail->AltBody = $params['msg_plain'];

		if (!$mail->send()) {
		    return array("error"=>true,"msg"=>$mail->ErrorInfo,"type"=>"smtp");
		} else {
		    return true;
		}
	}
}
?>
