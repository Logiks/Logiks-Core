<?php
/*
 * This class extends Exception and is meant to replace all Logiks Based Error and Exception
 * Based on the attributes of this object, framework will decide actions to be taken.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */

if(!defined('ROOT')) exit('No direct script access allowed');

class LogiksException extends Exception {

  protected $resource =null;

  public function __construct($errorCode = 0, $message = null, $resource = null, Exception $previous = null) {
    $this->resource=$resource;

    parent::__construct($message, $errorCode, $previous);
  }
}
?>
