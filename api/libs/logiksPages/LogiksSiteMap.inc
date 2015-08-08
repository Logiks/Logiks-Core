<?php
/*
 * LogiksSiteMap maps the url-slug structure into a chached system of page urls which can 
 * then be used to generate menus, find page, redirect pages etc.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 24/02/2012, 05/07/2015
 * Version: 2.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class LogiksSiteMap {

  private $site=null;

  public function __construct($site) {
    $this->site=$site;
  }

  public function findPage($page) {
    $page=explode("/",$page);
    $page=current($page);

    return $page;
  }
}
?>
