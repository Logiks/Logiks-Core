<?php
if(!defined('ROOT')) exit('No direct script access allowed');

loadModuleLib($this->params['scmd'], "api");
include $file;
?>