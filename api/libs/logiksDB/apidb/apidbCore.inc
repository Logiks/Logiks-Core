<?php
/*
 * Confiuration File Type (cfg,dat,lst,ini,xml,json) reader
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 * Dated: 01-08-2019
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class APIDBCore {

	protected $apiHOST=null;
	protected $apiParams = [
		
	];

	protected $apiHeaders = [
		
	];

    public function __construct($host, $params = []) {
		$this->apiHOST = $host;

		$this->apiParams = array_merge($this->apiParams,$params);

		return $this;
	}

	//GET
	public function api_get($uri) {

	}

	//FETCH
	public function api_fetch($uri) {

	}

	//CREATE
	public function api_post($uri) {

	}

	//UPDATE
	public function api_put($uri) {

	}

	//DELETE
	public function api_delete($uri) {

	}

}