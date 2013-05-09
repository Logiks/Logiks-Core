<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("generatePassword")) {
	function generatePassword($length=7) {
		//return rand(11111,999999);
		return substr(md5(rand()), 0, $length);
		//return generatePasswordX();
	}
	function generatePasswordX($length=9, $strength=0) {
		$vowels = 'aeuy';
		$consonants = 'bdghjmnpqrstvz';
		if ($strength & 1) {
			$consonants .= 'BDGHJLMNPQRSTVWXZ';
		}
		if ($strength & 2) {
			$vowels .= "AEUY";
		}
		if ($strength & 4) {
			$consonants .= '123456789';
		}
		if ($strength & 8) {
			$consonants .= '!@#$%^&';
		}
		$password = '';
		$alt = time() % 2;
		for ($i = 0; $i < $length; $i++) {
			if ($alt == 1) {
				$password .= $consonants[(rand() % strlen($consonants))];
				$alt = 0;
			} else {
				$password .= $vowels[(rand() % strlen($vowels))];
				$alt = 1;
			}
		}
		return $password;
	}
	function generatePasswordY($length=7, $strength=0) {
		$arrChars=array();
		$arrChars[0]=array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
		$arrChars[1]=array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
		$arrChars[2]=array("0","1","2","3","4","5","6","7","9","9");
		$arrChars[3]=array("!","@","#","$","%","^","&");
		$arrChars[4]=array("*","_","-","+","=",".");
		$arrChars[5]=array("{","}","(",")","[","]",";",":",",","?","/","\\");

		if($strength>count($arrChars)-1) {
			$strength=count($arrChars)-1;
		}
		
		$phrase="";
		for($i=0;$i<$length;$i++){
			$n=rand(0,$strength);
			$p=rand(0,count($arrChars[$n])-1);
			$phrase.=$arrChars[$n][$p];
		}
		return $phrase;
	}
	function checkPasswordStrength($pwd) {
		$strength = array("Blank","Very Weak","Weak","Medium","Strong","Very Strong");
		$score = 1;

		if (strlen($pwd) < 1) {
			return $strength[0];
		}
		if (strlen($pwd) < 4) {
			return $strength[1];
		}		
		if (strlen($pwd) >= PWD_MIN_LENGTH) {
			$score++;
		}
		if (strlen($pwd) >= 12) {
			$score++;
		}
		if (preg_match("/[a-z]/", $pwd) && preg_match("/[A-Z]/", $pwd)) {
			$score++;
		}
		if (preg_match("/[0-9]/", $pwd)) {
			$score++;
		}
		if (preg_match("/.[!,@,#,$,%,^,&,*,?,_,~,-,£,(,)]/", $pwd)) {
			$score++;
		}
		return($strength[$score]);
	}
	function testPassword($password) {
		/*** check if password is not all lower case ***/
		if(strtolower($password) != $password) {
			$strength += 1;
		}
		/*** check if password is not all upper case ***/
		if(strtoupper($password) == $password) {
			$strength += 1;
		}

		/*** check string length is 8 -15 chars ***/
		if($length >= 8 && $length <= 15) {
			$strength += 1;
		}
		/*** check if lenth is 16 - 35 chars ***/
		if($length >= 16 && $length <=35) {
			$strength += 2;
		}

		/*** check if length greater than 35 chars ***/
		if($length > 35) {
			$strength += 3;
		}
		/*** get the numbers in the password ***/
		preg_match_all('/[0-9]/', $password, $numbers);
		$strength += count($numbers[0]);

		/*** check for special chars ***/
		preg_match_all('/[|!@#$%&*\/=?,;.:\-_+~^\\\]/', $password, $specialchars);
		$strength += sizeof($specialchars[0]);

		/*** get the number of unique chars ***/
		$chars = str_split($password);
		$num_unique_chars = sizeof(array_unique($chars) );
		$strength += $num_unique_chars * 2;

		/*** strength is a number 1-10; ***/
		$strength = $strength > 99 ? 99 : $strength;
		$strength = floor($strength / 10 + 1);

		return $strength;
	}	
}

?>
