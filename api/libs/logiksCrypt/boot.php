<?php
/*
 * This file contains the functions for handling encryption in Logiks.
 * This started because of PHP 5-7 migration and removal of mcrypt from all future php releases.
 * This library is introduce uniform mechanisim for encrypting and decrypting in Logiks
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');


if(!function_exists("_cryptEncode")) {
  
  function _cryptEncode($msg) {
    
  }
  
  function _cryptDecode($payload) {
    
  }
}