<?php
/*
 * This class is used for Local File Access. This is just a basic driver. 
 * Other drivers are more sophisticated. Using this driver allows you to switch
 * between various drivers at runtime.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(is_dir(ROOT.VENDORS_FOLDER."PHPMailer/")) {
	$fs=[
			ROOT.VENDORS_FOLDER."PHPMailer/PHPMailer/phpmailer.inc",
			ROOT.VENDORS_FOLDER."PHPMailer/PHPMailer/smtp.inc",
			ROOT.VENDORS_FOLDER."PHPMailer/PHPMailer/pop3.inc",
		];
	foreach ($fs as $f) {
		if(file_exists($f)) include_once $f;
	}
}
class SMTPMSGDriver extends LogiksMSGDriver {

	protected $mailObj=null;

	public function __construct($key,$mailConfig=null) {
 		parent::__construct($key,$mailConfig);

 		$mail = new PHPMailer();
		$mail->isSMTP();

		if(isset($mailConfig['debug']) && $mailConfig['debug']) {
			$mail->SMTPDebug = 3;                               // Enable verbose debug output
			$mail->Debugoutput = 'html';
		} elseif(MASTER_DEBUG_MODE) {
			$mail->SMTPDebug = 3;                               // Enable verbose debug output
			$mail->Debugoutput = 'html';
		}
	
		if($mailConfig['default_from']!=null && strlen($mailConfig['default_from'])>0) {
			$fromMsg=$mailConfig['default_from'];
		} else {
			$fromMsg=$mailConfig['smtp_username'];
		}

		$mail->CharSet = 'UTF-8';
		
		if(!isset($mailConfig['smtp_ssl'])) {
			$mailConfig['smtp_ssl']=array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            );
		}
		
		$mail->SMTPOptions = array(
            'ssl' => $mailConfig['smtp_ssl']
        );

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

		$mail->setFrom($fromMsg);

		$this->mailObj=$mail;

		return $this;
 	}

	public function send($to,$subject,$msgBody,$params=[]) {
 		if(is_array($to)) {
			foreach($to as $t) $this->mailObj->addAddress($t);
		} else {
			$this->mailObj->addAddress($to);
		}

		$this->mailObj->Subject = $subject;
		$this->mailObj->msgHTML($msgBody);
		
		if(isset($params['msg_plain'])) $this->mailObj->AltBody = $params['msg_plain'];
		else $this->mailObj->AltBody = strip_tags($msgBody);

		if(isset($params['from']) && strlen($params['from'])>0) {
	    	$this->mailObj->setFrom($params['from']);
	    }

		if(isset($params['bcc'])) {
			if(!is_array($params['bcc'])) $params['bcc']=explode(",",$params['bcc']);
			foreach($params['bcc'] as $m) {
				$this->mailObj->addBCC($m);
			}
		}
		
		if(isset($params['cc'])) {
			if(!is_array($params['cc'])) $params['cc']=explode(",",$params['cc']);
			foreach($params['cc'] as $m) {
				$this->mailObj->addCC($m);
			}
		}
		
		if(isset($params['attachments'])) {
			if(!is_array($params['attachments'])) $params['attachments']=explode(",",$params['attachments']);
			foreach($params['attachments'] as $m) {
				$this->mailObj->addAttachment($m);
			}
		}
 		if (!$this->mailObj->send()) {
				$this->msgLog(false,$this->mailObj->ErrorInfo,$to,$subject,$msgBody,$params);
		    return array("error"=>true,"msg"=>$this->mailObj->ErrorInfo,"type"=>"smtp");
		} else {
				$this->msgLog(true,"Mail send successfully to {$to} with subject {$subject}",$to,$subject,$msgBody,$params);
		    return true;
		}
 	}
}
?>