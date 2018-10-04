<?php
/*
 * This class is used for S3 File Access. This is just a basic S3 driver. 
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */

 @include_once ROOT.VENDORS_FOLDER."aws/aws/s3.inc",
 
 class s3FSDriver extends LogiksFSDriver {

 	protected $bucket=null;

 	public function __construct($key,$params=null) {
 		if($params['basedir']==null || strlen($params['basedir'])<=1) {
			$params['basedir'] = "/";
		}

		if($params['bucket']!=null && strlen($params['bucket'])>0) {
			$this->bucket = $params['bucket'];
		} else {
			trigger_logikserror("S3 Driver needs a bucket to start with");
			return;
		}
		
 		parent::__construct($key,$params);
 	}
 	
 	public function __destruct() {
 	}

 	public function reset() {
 		$this->CURRENT_PATH=$this->BASE_PATH;
 	}

 	public function info($path=null) {trigger_logikserror("Current FS Driver Does Not Support This");}
 }
 ?>