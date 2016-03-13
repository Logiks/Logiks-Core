<?php
/*
 * This file contains functions for Loading Vendors
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('loadVendor')) {
  function loadVendor($vendor,$notMandatory=false) {
		if(is_array($vendor)) {
			foreach($vendor as $m) loadModule($m,$notMandatory);
		} else {
      if(strlen($vendor)<=0) return false;

      $fpath=checkVendor($vendor);

      if($fpath && strlen($fpath)>0) {
        include $fpath;
        return true;
  		} else {
  			if(MASTER_DEBUG_MODE && !$notMandatory) {
  				trigger_logikserror("Vendor Not Found :: " . $vendor,E_LOGIKS_ERROR,404);
  			}
  		}
  		return false;
		}
	}
  function checkVendor($vendor) {
    if(strlen($vendor)<=0) return false;

    $vendor=strtolower($vendor);

    $cachePath=_metaCache("VENDORS",$vendor);

    if(!$cachePath || !file_exists($cachePath)) {
      $vendorPath=getLoaderFolders('vendorPath',"vendors");

      $fpath="";
			foreach($vendorPath as $a) {
				$f1=ROOT . $a . $vendor . "/boot.php";
        $f2=ROOT . $a . $vendor . "/$vendor.php";
				if(file_exists($f1)) {
          $fpath=$f1;
					break;
				} elseif(file_exists($f2)) {
          $fpath=$f2;
          break;
        } else {
					$fpath="";
				}
			}
      if(strlen($fpath)>0) {
				_metaCacheUpdate("VENDORS",$vendor,$fpath);
				return $fpath;
			} else {
				return false;
			}
    } else {
      return $cachePath;
    }
  }
}
?>
