<?php
/*
 * Logiks Implementation of JSON Web Token.
 * It adds another layer of encryption to payload for secure transfer of data between devices during transactions
 *
 * Usage:
 * $secretKey = md5(time().rand());         //PRIVATE
 * $secretIV = md5(time().rand());          //PUBLIC 
 *
 * $jwt = new LogiksJWT($secretKey, $secretIV);
 * 
 * $dataToken = $jwt->generateToken("Hello World");
 * 
 * $data1 = $jwt->decodeToken($dataToken);
 * 
 * var_dump($dataToken);
 * echo "<hr>";
 * var_dump($data1);
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 10/11/2018
 * Version: 1.0
 */
class LogiksJWT {
  
  private $secretKey = false;       //PRIVATE
  private $secretIV = false;        //PUBLIC
  
  public function __construct($secretIV=false) {
    if(!$secretIV) {
      $headers = getAllHeaders();
      if(isset($headers['pubkey'])) {
        $secretIV = md5($headers['pubkey']);
      } elseif(isset($_SESSION['SESS_USER_ID'])) {
        $secretIV = md5($_SESSION['SESS_USER_ID']);
      } elseif(isset($_SESSION['SESS_USERID'])) {
        $secretIV = md5($_SESSION['SESS_USERID']);
      } else {
        $secretIV = md5(session_id());//md5(time().rand());
      }
    }
    
    $jconfigSecret = getConfig("JWT_SECRET");
    $jconfigIV = getConfig("JWT_IV");
    
    if(!$jconfigSecret || $jconfigSecret=="random") {
      if(!isset($_SESSION['JWT_SECRET_KEY'])) {
        $_SESSION['JWT_SECRET_KEY'] = md5(time().rand());;
      }
      
      $secretKey = $_SESSION['JWT_SECRET_KEY'];
    } else {
      $secretKey = $jconfigSecret;
    }
    
    $this->secretKey = $secretKey;
    $this->secretIV = $secretIV;
    
//     printArray([$secretKey,$secretIV]);
  }
  
  public function generateToken($data=[],$algo="HS256") {
    if(!is_array($data)) {
      $data = ["msg"=>$data];
    }
    
    // Create token header as a JSON string
    $header = json_encode(['typ' => 'JWT', 'alg' => $algo]);

    // Create token payload as a JSON string
    $payload = json_encode(array_merge([
                "jti"=>md5(rand().time().SiteLocation.SITENAME),
                "iss" => SiteLocation,
          //       "sub"=> "",
                "aud" => SiteLocation,
                "scope"=> "*",
                "iat" => time(),
                "exp" => time()+3600,
                "nbf" => time()+360000,
                ],$data));

    // Encode Header to Base64Url String
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

    // Encode Payload to Base64Url String
//     $base64UrlPayload = base64_encode($payload);
    $base64UrlPayload = $this->enc_dec("encrypt", $payload);
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], $base64UrlPayload);

    // Create Signature Hash
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secretKey, true);

    // Encode Signature to Base64Url String
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    // Create JWT
    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

    return $jwt;
  }
  
  public function decodeToken($token) {
    $tokenArr = explode(".",$token);
    
    $header = json_decode(base64_decode($tokenArr[0]),true);
    
    $payload = $this->enc_dec("decrypt", $tokenArr[1]);
    $payload = json_decode($payload,true);
    
    $signature = $tokenArr[2];
    
    // Calculate Signature Hash
    $signatureCalculated = hash_hmac('sha256', $tokenArr[0] . "." . $tokenArr[1], $this->secretKey, true);
    // Encode Signature to Base64Url String
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signatureCalculated));
    
    if($signature == $base64UrlSignature) {
      return $payload;
    } else {
      return false;
    }
  }
  
  /**
 * Encrypt a message
 * 
 * @param string $message - message to encrypt
 * @param string $key - encryption key
 * @return string
 * @throws RangeException
 */
  private function enc_dec( $action, $message) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = $this->secretKey;
    $secret_iv = $this->secretIV;
    // hash
    $key = hash('sha256', $secret_key);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ( $action == 'encrypt' ) {
      $output = openssl_encrypt($message, $encrypt_method, $key, 0, $iv);
      $output = base64_encode($output);
    } else if( $action == 'decrypt' ) {
      $output = openssl_decrypt(base64_decode($message), $encrypt_method, $key, 0, $iv);
    }
    return $output;
  }
}
?>