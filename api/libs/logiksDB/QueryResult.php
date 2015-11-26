<?php
/*
 * Query Result container. This contains the query results for future references.
 * 
 * Author: Bismay Kumar Mohapatra (bismay@openlogiks.org)
 * Author: Kshyana Prava (mita@openlogiks.org)
 * Version: 2.0
 */

class QueryResult {
	
	private $keyName=null;
	private $resultSet=null;
	
	public function __construct($key,$result) {
		$this->keyName=$key;
		$this->resultSet=$result;
	}
	
	public function getInstanceName() {
		return $this->keyName;
	}
	public function getResult() {
		return $this->resultSet;
	}
	
	public function purge() {
		unset($this->resultSet);
	}
}
?>
