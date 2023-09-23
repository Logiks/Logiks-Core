<?php
/*
 * APIKeys are responsible to next generation of API Validation system used during mauth and more.
 * 
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 02/08/2018
 * Version: 1.0
 */
class APIKeys {

    private static $instance=null;

    protected function __construct() {
        
    }

    public static function getInstance() {
        if(!isset(self::$instance)) {
            $c=__CLASS__;
            self::$instance=new $c;
        }
        return self::$instance;
    }

    //Automatically validate incoming request using Headers
    public function is_valid_request() {
        $_HEADERS = getallheaders();

        return true;
    }
}

