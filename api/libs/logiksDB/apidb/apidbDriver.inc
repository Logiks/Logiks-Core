<?php
/*
 * Driver For APIDB Core
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 2.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

include_once "./apidbCore.inc";

class apidbDriver extends AbstractDBDriver {

   public function __construct($keyName,$params) {
      parent::__construct($keyName,$params);
   }
}