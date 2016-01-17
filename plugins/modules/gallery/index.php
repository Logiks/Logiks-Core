<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("getGalleryImages")) {
	
	function getGalleryImages($galId,$index=0,$limit=100,$greedyOnLarge=false,$greedyOnThumbs=false,$autoImageTagOnGreedy=true,
			$bPath="galleries/") {
		$baseDir=APPROOT.APPS_MEDIA_FOLDER.$bPath;
		$bannerDir=$baseDir.$galId."/large/";
		$textDir=$baseDir.$galId."/text/";
		$thumbDir=$baseDir.$galId."/thumbs/";
		$lnkDir=$baseDir.$galId."/lnks/";

		if(!is_dir($bannerDir)) {
			if(mkdir($bannerDir,0777,true)) {
				chmod($bannerDir,0777);
			}
		}
		if(!is_dir($textDir)) {
			if(mkdir($textDir,0777,true)) {
				chmod($textDir,0777);
			}
		}
		if(!is_dir($thumbDir)) {
			if(mkdir($thumbDir,0777,true)) {
				chmod($thumbDir,0777);
			}
		}
		if(is_dir($bannerDir)) {
			$gallery=array("large"=>array(),"thumb"=>array(),"text"=>array(),"link"=>array(),"config"=>array());

			$bs=scandir($bannerDir,1);
			unset($bs[count($bs)-1]);unset($bs[count($bs)-1]);
			$bs=array_reverse($bs);
			$maxPhotos=count($bs);

			if($index>$maxPhotos) $index=$maxPhotos-$limit;
			if($index+$limit>$maxPhotos) $limit=$maxPhotos-$index;

			$gallery['config']=parseConfigFile($baseDir.$galId."/config.cfg");
			foreach($gallery['config'] as $a=>$b) {
				$gallery['config'][$a]=$b['value'];
			}

			for($i=$index;$i<$index+$limit;$i++) {
				$a=$bs[$i];
	
				$bf=getWebPath($bannerDir.$a).$a;
	
				$pathInfo=pathinfo($bf);
				$ext=$pathInfo["extension"];
				$fname=$pathInfo["filename"];
	
				if($greedyOnLarge) {
					$data=file_get_contents($bannerDir.$a);
					$data=base64_encode($data);
					$mime="image/$ext";
					if($autoImageTagOnGreedy)
						$data="<img src='data:$mime;charset=utf-8;base64,{$data}' alt='Gallery Photo' with=100% height=100% />";
					else
						$data=array("mime"=>$mime,"image"=>$data);
					array_push($gallery['large'],$data);
				} else {
					array_push($gallery['large'],$bf);
				}
	
				$msgTxt="";
				$msgLnk="";
				if(file_exists("{$textDir}{$fname}.html") && is_readable("{$textDir}{$fname}.html")) {
					$msgTxt=trim(file_get_contents("{$textDir}{$fname}.html"));
				}
				if(file_exists("{$lnkDir}{$fname}.lnk") && is_readable("{$lnkDir}{$fname}.lnk")) {
					$msgLnk=trim(file_get_contents("{$lnkDir}{$fname}.lnk"));
				}
				if(strlen($msgLnk)<=0) $msgLnk="#";
				elseif(strpos("{$msgLnk}","://")<1) {
					$msgLnk=SiteLocation.$msgLnk;
				}
				array_push($gallery['text'],$msgTxt);
				array_push($gallery['link'],$msgLnk);
	
				$thumbFile="";
				if(file_exists($thumbDir.$a) && is_readable($thumbDir.$a)) {
					$thumbFile=$thumbDir.$a;
				} elseif(file_exists("{$thumbDir}{$fname}.png") && is_readable("{$thumbDir}{$fname}.png")) {
					$a="{$fname}.png";
					$thumbFile=$thumbDir.$a;
				}
				if($greedyOnThumbs) {
					$pathInfo=pathinfo($thumbFile);
					$ext=$pathInfo["extension"];
	
					$data=file_get_contents($thumbFile);
					$data=base64_encode($data);
					$mime="image/$ext";
					if($autoImageTagOnGreedy)
						$thumbData="<img src='data:$mime;charset=utf-8;base64,{$data}' alt='Thumb Photo' with=100% height=100% />";
					else
						$thumbData=array("mime"=>$mime,"image"=>$data);
					array_push($gallery['thumb'],$thumbData);
				} else {
					$thumbFile=getWebPath($thumbFile).basename($thumbFile);
					array_push($gallery['thumb'],$thumbFile);
				}
			}
			return $gallery;
		} else {
			if(MASTER_DEBUG_MODE=='true') trigger_error("Gallery/Banner Not Found :: " . $galId);
		}
		return array();
	}
}
?>
