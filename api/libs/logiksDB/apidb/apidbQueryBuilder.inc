<?php
/*
 * Query Generator For APIDB Core
 * 
 * Author: Bismay Mohapatra bismay4u@gmail.com
 * Version: 1.0
 * Dated: 01-08-2019
 */
if(!defined('ROOT')) exit('No direct script access allowed');

include_once "./apidbCore.inc";

class apidbQueryBuilder extends QueryBuilder {

	public function __construct($dbInstance) {
		parent::__construct($dbInstance);
	}
}
?>
