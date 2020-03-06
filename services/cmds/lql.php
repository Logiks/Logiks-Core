<?php
if(!defined('ROOT')) exit('No direct script access allowed');

//Logiks Query Language
//Based on GraphQL, LQL will allow mobile apps to do multi service/api query to system with change in state support for caching ability
//This will allow a faster and more efficient mobile app and multi enviroment API support

//Query Format :
//services/lql/fetch?queryid=asd-qwe-zxc

handleActionMethodCalls();

function _service_isupdated() {
	if(isset($_GET['queryid'])) {
		$queryID = $_GET['queryid'];

		$query = find_query($queryID);
		$queryData = fetch_query($query);

		printServiceMsg($queryData);
	} elseif(isset($_GET['query'])) {
		$query = explode("-", $_GET['query']);

		$queryData = fetch_query($query);
		
		printServiceMsg($queryData);
	} else {
		printServiceMsg([], 200, [
			"Error"=>"Query Not Defined"
		]);
	}
}

function _service_fetch() {
	if(isset($_GET['queryid'])) {
		$queryID = $_GET['queryid'];
	} elseif(isset($_GET['query'])) {
		$query = explode("-", $_GET['query']);
	} else {
		printServiceMsg([], 200, [
			"Error"=>"Query Not Defined"
		]);
	}
}

function find_query($queryID) {
	return false;
}

function fetch_query($query) {
	return false;
}
?>
