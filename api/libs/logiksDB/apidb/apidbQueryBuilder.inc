<?php
/*
 * SQL Query Generator For APIDB Core
 * 
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 2.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

include_once "./apidbCore.inc";

class apidbQueryBuilder extends QueryBuilder {

	public function __construct($dbInstance) {
      parent::__construct($dbInstance);
   }
}
?>
