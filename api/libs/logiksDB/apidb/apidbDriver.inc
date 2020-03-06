	<?php
/*
 * Driver For APIDB Core
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 * Dated: 01-08-2019
 */
if(!defined('ROOT')) exit('No direct script access allowed');

include_once "./apidbCore.inc";

class apidbDriver extends AbstractDBDriver {

	public function __construct($keyName,$params) {
		parent::__construct($keyName,$params);

		if(!isset($params['host'])) {
			trigger_logikserror("Database ERROR, host not found For {$keyName}");
		}

		$this->link = new APIDBCore($params['host'],$params);
	}

	public function open($keyName) {
		
	}
	public function close() {

	}
	public function runQuery($sql) {

	}
	public function runCommandQuery($q) {

	}

	public function fetchData($result,$format="assoc") {

	}
	public function fetchHeaders($result) {

	}
	public function fetchFields($result) {

	}
	public function get_fieldType($result, $colIndex) {

	}
	public function get_recordCount($result) {

	}
	public function get_columnCount($result) {

	}
	public function get_errorNo() {

	}
	public function get_error() {

	}
	public function get_affectedRows() {

	}
	public function get_insertID() {

	}
	public function get_dbinfo() {

	}
	public function get_dbstatus() {

	}
	public function get_tableStatus() {

	}
	public function get_dbObjects() {

	}
	public function get_defination($table) {

	}
	public function get_tableList() {

	}
	public function get_columnList($table,$nameOnly=true) {

	}
	public function get_primaryKey($table) {

	}
	public function get_allkeys($table) {

	}
	public function get_schema($tables=null,$dropIfExists=true) {

	}
	public function prep_query($sql) {

	}
}