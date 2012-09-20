<?php
if(!defined('ROOT')) exit('No direct script access allowed');

class ImageProps {
   
   var $image;
   var $image_type;
   
   function getImage() {
	   return $this->image;
   }
   function setImage($image) {
	   $this->image=$image;
   }
   
   function load($filename) {
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
         $this->image = imagecreatefrompng($filename);
      }
   }
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
      if($image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image,$filename);
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image,$filename);
      }   
      if( $permissions != null) {
         chmod($filename,$permissions);
      }
   }
   function clearImages() {
	   imagedestroy($this->image);
   }
   function output($image_type=IMAGETYPE_JPEG) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image);         
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image);
      }   
   }
   function getWidth() {
      return imagesx($this->image);
   }
   function getHeight() {
      return imagesy($this->image);
   }
   function resizeToHeight($height) {
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100; 
      $this->resize($width,$height);
   }
   function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;
   }
   function crop($x,$y,$width,$height) {
	   $new_image = imagecreatetruecolor($width, $height);
	   imagecopyresampled($new_image, $this->image,0,0,$x,$y,$width,$height,$width,$height);
	   $this->image = $new_image;
   }
   
   function createThumb($targetFile,$tsize=null) {
		if($tsize==null || !is_array($tsize)) {
			$tsize=getConfig("THUMBNAIL_SIZE");
			$tsize=explode(",",$tsize);
			if(count($tsize)==0) {
				$tsize[0]=48;
				$tsize[1]=48;
			} elseif(count($tsize)==1) {
				$tsize[1]=$tsize[0];
			}
		}
		$this->resize($tsize[0],$tsize[1]);
		$imageType = $this->getImageType($targetFile);
		$this->save($targetFile,$imageType);
   }
   function getImageType($fname=null) {
	   if($fname==null) {
		   return $this->image_type;
	   }
	   $fname=explode(".",$fname);
	   $ext=strtoupper($fname[count($fname)-1]);
	   switch($ext) {
			case "PNG":
				return IMAGETYPE_PNG;
			case "GIF":
				return IMAGETYPE_GIF;
			case "JPG":
			case "JPEG":
				return IMAGETYPE_JPEG;
	   }
	   return $this->image_type;
   }
}
?>
