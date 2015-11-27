<?php
/*
 * Abstract Template Engine allows to connect more then one template engines into logiks.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 01/07/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class AbstractTemplateEngine {

	public function __construct() {
	}

	public function compileTemplate($tmpl,$data=array()) {}
	public function fetchTemplate($tmpl,$data=array()) {}

	public function clearCache($tmpl) {}
	public function isCached($tmpl) {}
		
	public function addVar($name,$value) {}
	public function getVar($name) {}
	public function removeVar($name) {}
}
?>
