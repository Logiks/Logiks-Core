<?php
/*
 * This file helps in generating RSS Feeds From database using SQL
 * //Template Params
 * #datatable#
 * #image#
 * 
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
 
if(!defined('ROOT')) exit('No direct script access allowed');

class RSSGen {
	private static $encoding="ISO-8859-1";
	private static $rssVersion="2.0";
	private static $rssGenerator="Logiks RSSGen v1";
	
	private $refLINK="";
	
	private $itemImageCol="image";
	
	private $itemLimit=15;
	
	private $useContentAsDescsIfDescEmpty=true;
	private $showContent=true;
	private $embedImageInContent=true;
	
	private $diplayTemplate=0;
	
	private $contentTemplates=array(
							"<table width=100% cellpadding=0 cellspacing=0 border=0 >
								 <tr valign=top align=left><td>#datatable#</td><td align=right>#image#</td></tr>
							 </table>",
							 "#datatable#",
							 "<table width=100% cellpadding=0 cellspacing=0 border=0 >
								 <td align=center>#image#</td></tr>
							 </table>"
						);
						
	public function __construct() {
		$this->refLINK=SiteLocation.SITENAME."/";
	}
	public static function listFeeds($table) {
		$list=array();
		$sql="SELECT rssid,title,category,language,author FROM $table where (site='".SITENAME."' OR site='*') AND blocked <> 'true'";
		$res=_dbQuery($sql);
		if($res) {
			$list=_dbData($res);
			_dbFree($res);
		} else {
			$list["error"]="No Found";
		}
		return $list;
	}
	public static function listTemplates() {
		return array("Data+Image","Data Only","Image Only");
	}
	public static function listAttributes() {
		return array(
				"limit","href","template","embedImage","contentAsEmptyDescription","showContent",
				"showEditor","showWebMaster",
			);
	}
	
	public static function printRSSHeader() {
		header("Content-Type: application/rss+xml; charset=".RSSGen::$encoding);
	}
	public static function generateFromDB($table,$rssID,$diplayTemplate=null,$embedImageInContent=null,$itemLimit=null) {
		if(isset($GLOBALS['LOGIKS']["_SERVER"]['HTTPS'])) $host="https://"; else $host="http://";
		$host=$host.$GLOBALS['LOGIKS']["_SERVER"]["HTTP_HOST"]."/";
		
		$sql="SELECT * FROM $table where rssid='$rssID' AND (site='".SITENAME."' OR site='*') AND blocked <> 'true'";
		$res=_dbQuery($sql);
		if($res) {
			$data=_dbData($res);
			_dbFree($res);

			if(count($data)>0) {
				$rssGen=new RSSGen();
				
				$attr=array();
				if(strlen($data[0]['attributes'])>4) {
					$attr=json_decode($data[0]['attributes'],true);
					$rssGen->setAttributes($attr);
				}
				
				$rssGen->embedImageInContent($embedImageInContent);
				$rssGen->diplayTemplate($diplayTemplate);
				$rssGen->itemLimit($itemLimit);
				
				if(strlen($data[0]['ref_url'])>0) {
					$rssGen->refLINK($data[0]['ref_url']);
				}
				if(strlen($data[0]['doe'])<=0) {
					$data[0]['doe']=gmdate('D, d M Y H:i:s');
				}
				
				$xml='<?xml version="1.0" encoding="'.RSSGen::$encoding.'" ?>
						<rss version="'.RSSGen::$rssVersion.'" xml:base="'.$host.'" xmlns:dc="http://purl.org/dc/elements/1.1/">
						<channel>';
				$xml.=' <title>'.$data[0]['title'].'</title>
						<link>'.$rssGen->refLINK.'</link>
						<category>'.$data[0]['category'].'</category>
						<description>'.$data[0]['descs'].'</description>
						<language>'.$data[0]['language'].'</language>
						<copyright>'.getConfig('APPS_COMPANY').'</copyright>
						<generator>'.RSSGen::$rssGenerator.'</generator>
						<pubDate>'.gmdate('D, d M Y H:i:s').' GMT</pubDate>
						<lastBuildDate>'.$data[0]['doe'].' GMT</lastBuildDate>
						';
				if(isset($attr["showEditor"]) && $attr["showEditor"]=="true") {
					$xml.='<managingEditor>'.$data[0]['author'].'</managingEditor>';
				}
				if(isset($attr["showWebMaster"]) && $attr["showWebMaster"]=="true") {
					$xml.='<webMaster>'.getConfig('WEBMASTER_EMAIL') . ' ('.getConfig('APPS_COMPANY').')'.'</webMaster>';
				}
				
				$xml.=$rssGen->createImageTag($data[0]['title'],$data[0]['image_link'],$rssGen->refLINK);
				
				$xml.=$rssGen->enumerateRSSItemsFromDB($rssID,$data[0]['datatable_table'],$data[0]['datatable_cols'],$data[0]['datatable_where']
					,$data[0]['datatable_orderby'],$data[0]['author'],$data[0]['image_link'],$data[0]['image_width'],$data[0]['image_height']);
				
				$xml.='</channel>
				   </rss>';
			} else {
				$xml=RSSGen::generateErrorRSS();
			}
		} else {
			$xml=RSSGen::generateErrorRSS();
		}
		return $xml;
	}
	
	public static function generateErrorRSS($title="Feed Not Found",
			$descs="Sorry, the feed you are searching does not exist or has been removed.",
			$imageLink="media/images/notfound/rss.png") {
		
		if(isset($GLOBALS['LOGIKS']["_SERVER"]['HTTPS'])) $host="https://"; else $host="http://";
		$host=$host.$GLOBALS['LOGIKS']["_SERVER"]["HTTP_HOST"]."/";
		$refLINK=SiteLocation.SITENAME."/";
		$imageLink=SiteLocation.loadMedia($imageLink,true);
		
		$rssGen=new RSSGen();
		
		$xml='<?xml version="1.0" encoding="UTF-8" ?>
			  <rss version="2.0" xml:base="'.$host.'" xmlns:dc="http://purl.org/dc/elements/1.1/">
			  <channel>';
		$xml.=' <title>'.$title.'</title>
					<link>'.$refLINK.'</link>
					<category>RSS Error</category>
					<description>'.$descs.'</description>
					<language>en</language>
					<webMaster>'.getConfig('WEBMASTER_EMAIL') . ' ('.getConfig('APPS_COMPANY').')'.'</webMaster>
					
					<generator>Logiks RSS Engine v1</generator>
					<copyright>'.getConfig("APPS_COMPANY").'</copyright>
					<pubDate>'.gmdate('D, d M Y H:i:s').' GMT</pubDate>
					<lastBuildDate>'.gmdate('D, d M Y H:i:s').' GMT</lastBuildDate>
					';
		$xml.=$rssGen->createImageTag($title,$imageLink,$refLINK);
		
		$xml.='</channel>
			   </rss>';
		return $xml;
	}
	
	public function enumerateRSSItemsFromDB($rssID,$itemTable,$itemCols="*",$where="",$orderBy="",$author="",$image="",$imageWidth="",$imageHeight="") {
		$items="";
				
		$sql="SELECT $itemCols FROM $itemTable";
		if(strlen($where)>0) {
			$where=_replace($where);
			$sql.=" WHERE $where";
		}
		if(strlen($orderBy)>0) {
			$sql.=" ORDER BY $orderBy";
		}
		$sql.=" LIMIT ".$this->itemLimit;
		$res=_dbQuery($sql);
		$data=_dbData($res);
		_dbFree($res);
		//printArray($data);exit();

		if(count($data)>0) {
			foreach($data as $d) {
				if(!isset($d["author"])) {
					$d["author"]=$author;
				}
				if(!isset($d[$this->itemImageCol])) {
					$d[$this->itemImageCol]=$image;
				}
				
				$items.=$this->createItemTag($rssID,$d,$imageWidth,$imageHeight);
			}
		}
		
		return $items;
	}
	
	//Property Functions
	public function itemLimit($itemLimit=null) {
		if(is_numeric($itemLimit)) {
			$this->itemLimit=$itemLimit;
		}
		return $this->itemLimit;
	}
	public function itemImageCol($itemImageCol=null) {
		if($itemImageCol!=null) {
			$this->itemImageCol=$itemImageCol;
		}
		return $this->itemImageCol;
	}
	public function diplayTemplate($diplayTemplate=null) {
		if(is_numeric($diplayTemplate)) {
			$this->diplayTemplate=$diplayTemplate;
		}
		if($this->diplayTemplate>=count($this->contentTemplates)) {
			$this->diplayTemplate=count($this->contentTemplates)-1;
		}
		return $this->diplayTemplate;
	}
	public function refLINK($link=null) {
		if($link!=null) {
			$this->refLINK=$link;
		}
		return $this->refLINK;
	}
	public function embedImageInContent($embed=null) {
		if(is_bool($embed)) {
			$this->embedImageInContent=$embed;
		}
		return $this->embedImageInContent;
	}
	public function showContent($showContent=null) {
		if(is_bool($showContent)) {
			$this->showContent=$showContent;
		}
		return $this->showContent;
	}
	public function setAttributes($attribs=array()) {
		foreach($attribs as $a=>$b) {
			switch($a) {
				case "limit":
					$this->itemLimit($b);
					break;
				case "itemImageCol":
					$this->itemImageCol($b);
					break;
				case "href":
					$this->refLINK($b);
					break;
				case "template":
					$this->diplayTemplate($b);
					break;
				case "embedImage":
					$this->embedImageInContent($b);
					break;
				case "contentAsEmptyDescription":
					$this->useContentAsDescsIfDescEmpty=$b;
					break;
				case "showContent":
					$this->showContent($b);
					break;
			}
		}
	}
	
	//Individual Items
	private function createItemTag($rssID,$d,$imageWidth="",$imageHeight="") {
		$guid=md5("0");
		$title="No Title Found";
		$link=$this->refLINK;
		$author="";
		$image="";
		$descs="";
		$category="";
		$content="";
		$pubDate=gmdate('D, d M Y H:i:s');
		
		$items="";
		if(count($d)>0) {
			if(isset($d["title"])) {
				$title=$d["title"];
			}
			if(isset($d["link"])) {
				$link=$d["link"];
				unset($d['link']);
			} elseif(isset($d["url"])) {
				$link=$d["url"];
				unset($d['url']);
			} elseif(isset($d["href"])) {
				$link=$d["href"];
				unset($d['href']);
			}
			if(isset($d["author"])) {
				$author=$d["author"];
				unset($d['author']);
			}
			if(isset($d["category"])) {
				$category=$d['category'];
			}
			if(isset($d[$this->itemImageCol])) {
				$image=$d[$this->itemImageCol];
				unset($d[$this->itemImageCol]);
			}
			if(!isset($d["description"])) {
				if(isset($d["descs"])) {
					$descs=$d["descs"];
				}
			} else {
				$descs=$d["description"];
			}
			if(isset($d["pubdate"])) {
				$pubDate=$d["pubdate"];
			}
			
			if(isset($d["content"])) {
				$content=$d['content'];
			} else {
				$datatable="<table width=100% cellpadding=2 cellspacing=0 border=0>";
				foreach($d as $a=>$b) {
					$b=stripcslashes($b);
					$datatable.="<tr><th width=100px align=left>".toTitle($a)."</th><td>$b</td></tr>";
				}
				$datatable.="</table>";
				
				$content="";
				$imageURL="";
				
				$content=$this->contentTemplates[$this->diplayTemplate];
				
				if($this->embedImageInContent) {
					if(strlen($image)>0) {
						$imageURL=loadMedia(trim($image));
						$content=str_replace("#image#","<img src='".$imageURL."' />",$content);
					}
				}
				
				$content=str_replace("#datatable#",$datatable,$content);
				$content=str_replace("#image#","",$content);
			}
			if(isset($d["guid"])) {
				$guid=$d["guid"];
			} else {
				$guid=md5(implode("#",$d));
			}
			/*if($link!=$this->refLINK && isset($d['id'])) {
				if(strpos($link,"?")>1) {
					$link.="&rssid={$rssID}&rssref={$d['id']}";
				} else {
					$link.="?rssid={$rssID}&rssref={$d['id']}";
				}
				$link=str_replace("&&","&",$link);
			}*/
			/*
			if($link!=$this->refLINK && isset($d['id'])) {
				$link+="&rssid={$rssID}&rssref={$d['id']}";
				$link=str_replace("&&","&",$link);
			}
			$items='<item>';
			$items.='<title>'.$title.'</title>
					<link>'.$link.'</link>
					<pubDate>'.gmdate('D, d M Y H:i:s').' GMT</pubDate>
					<guid isPermaLink="false">'.$guid.'/</guid>
					<source url="'.$this->refLINK.'">'.SITENAME.'</source>
				   ';//$this->refLINK.'rssnews/'._randomId()
			*/
			$items='<item>';
			$items.='<title>'.strip_tags($title).'</title>
					<link>'.$link.'</link>
					<pubDate>'.$pubDate.' GMT</pubDate>
					<guid isPermaLink="false">'.$guid.'</guid>
					<source url="'.$this->refLINK.'">'.SITENAME.'</source>
				   ';//$this->refLINK.'rssnews/'._randomId()
			if(strlen($descs)>0) {
				$items.='<description><![CDATA['. $descs .']]></description>
				   ';
			} elseif($this->useContentAsDescsIfDescEmpty) {
				$items.='<description><![CDATA['. $content .']]></description>
				   ';
			}
			if(strlen($content)>0 && $this->showContent) {
				$items.='<content:encoded xmlns:content="http://purl.org/rss/1.0/modules/content/"><![CDATA['.$content.']]></content:encoded>
				   ';
			}
			if(strlen($author)>0) {
				//<author>'.$author.'</author>
				$items.='<dc:creator xmlns:dc="http://purl.org/dc/elements/1.1/">'.$author.'</dc:creator>
						';
			}
			if(strlen($category)>0) {
				if(!is_array($category)) $category=explode(",",$category);
				foreach($category as $a) {
					$items.="<category>$a</category>
					";
				}
			}
			if(!$this->embedImageInContent) {
				$items.=$this->createImageTag($d['title'],$image,$link,$imageWidth,$imageHeight);
				//$items.=$this->createEnclosureTag($image);
			}
			$items.='</item>
						';
		}
		return $items;
	}
	private function createImageTag($title,$imageURL,$refLink="",$width="",$height="") {
		$items="";
		if(strlen($title)>0 && strlen($imageURL)>0) {
			$imageURL=loadMedia($imageURL);
			if(strlen($refLink)<=0) {
				$refLink=$this->refLINK;
			}
			$items="<image>
					<title>$title</title>
					<url>$imageURL</url>
					<link>$refLink</link>
					";
			if(strlen($width)>0 && $width>0) $items.="<width>$width</width>";
			if(strlen($height)>0 && $height>0) $items.="<height>$height</height>";
			$items.="</image>
						";
		}
		return $items;
	}
	private function createEnclosureTag($imageURL) {
		$items="";
		if(strlen($imageURL)>0) {
			$imageSize=filesize(APPROOT.$imageURL);
			$imageURL=loadMedia($imageURL);
			$mime=explode(".",$imageURL);
			$mime="image/".$mime[count($mime)-1];
			$items='<enclosure url="'.$imageURL.'" length="'.$imageSize.'" type="'.$mime.'" ></enclosure>';
		}
		return $items;
	}
}
?>
