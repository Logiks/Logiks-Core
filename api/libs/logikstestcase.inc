<?php
/*
 * Logiks Test Case Class that is required to run PHPUnit tests for Logiks Testkit
 *
 * Author: Bismay Kumar Mohapatra [bismay4u@gmail.com]
 * Version: 2.0
 */

class LogiksTestCase  extends PHPUnit_Framework_TestCase {
  
	public function http_get($url) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    return [$result,$code,$type];
  }
}
?>

