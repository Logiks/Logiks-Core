<?php
/*
 * ServiceAuthEngine is an abstract class for testing AuthKeys for remote API connections
 * any class that intends to provide authkey service has to extend this class.
 * 
 * @author : Bismay Kumar Mohapatra
 * @version : 1.0
 */
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

class ServiceAuthEngine {

	public function testAPIKey($apikey) {return false;}
}
?>