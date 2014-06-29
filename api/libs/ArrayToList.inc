<?php
/*
 * This Class basically converts all kinds of multidimensional arrays to Tree Lists.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 24/02/2012
 * Author: Kshyana Prava kshyana23@gmail.com on 24/02/2012
 * Version: 1.0
 */
class ArrayToList {

	private $maxTags=4;
	private $format="ul,li,h2,a";
	private $colDefn=null;
	private $printTitle=true;

	public function __construct() {
		$this->colDefn=ArrayToList::getColumnTagsHolder();
		$this->listTags($this->format);
	}

	public function printTitle($print=null) {
		if($print!==null) $this->printTitle=$print;
		return $this->printTitle;
	}

	public function colDefns($colDefn) {
		if($colDefn!=null) {
			$this->colDefn=$colDefn;
		} else {
			$this->colDefn=ArrayToList::getColumnTagsHolder();
		}
		return $this->colDefn;
	}
	public function listTags($format) {
		if($format!=null) {
			if(!is_array($format)) $this->format=explode(",",$format);
			else $this->format=$format;
			if(sizeOf($this->format)<$this->maxTags) {
				for($i=sizeOf($this->format);$i<$this->maxTags;$i++) {
					array_push($this->format,"span");
				}
			}
		}
		return $this->format;
	}

	public function getTree($treeArray,$dataStr="data",$depth=0,$showID=false) {
		if(sizeOf($treeArray)<=0) return "";
		$colData=array();
		$s="";

		$format=$this->format;
		$colDefn=$this->colDefn;

		foreach($treeArray as $a=>$b) {
			$claz="";
			$tips="";
			$colData=array();
			$data=array();
			if(is_array($b)) {
				$hasData=array_key_exists($dataStr,$b);
				if($hasData) {
					$data=$b[$dataStr];
					unset($b[$dataStr]);

					if(isset($colDefn["classCol"]) && isset($data[$colDefn["classCol"]]) && !is_array($data[$colDefn["classCol"]])) $claz="class='{$data[$colDefn['classCol']]}'";
					if(isset($colDefn["tipsCol"]) && isset($data[$colDefn["tipsCol"]]) && !is_array($data[$colDefn["tipsCol"]])) $tips="title=\"{$data[$colDefn['tipsCol']]}\"";

					foreach($colDefn as $m=>$n) {
						if(isset($data[$n])) {
							if($m=="linkCol" && $data[$n]=="#") $colData[$m]="";
							else $colData[$m]=$data[$n];
						}
					}
				}
				$n=count($b);
				if($n>0) {
					//$colData[$colDefn["titleCol"]]
					$as=$this->createTag($format[3],$a,$colData,$showID);
					if($depth==0) {
						$s.="<{$format[1]} class='dropdown'>";
						$s.="<{$format[2]} class='dropdown-toggle' data-toggle='dropdown'>{$as}<strong class='caret'></strong></{$format[2]}>";
					} else {
						$s.="<{$format[1]}>{$as}";
					}
					$s.=$this->getTree($b,$dataStr,$depth+1,$showID);
					$s.="</{$format[1]}>";
				} else {
					if(is_array($data[$colDefn["linkCol"]])) {
						$err=array();
						foreach($data as $q=>$w) {
							foreach($w as $p=>$o) {
								$err[$p][$q]=$o;
							}
						}
						foreach($err as $x) {
							$s1=$x[$colDefn["titleCol"]];
							$y=array(
										//"idCol"=>$x[$colDefn["idCol"]],
										"linkCol"=>$x[$colDefn["linkCol"]],
										"iconCol"=>$x[$colDefn["iconCol"]],
										"tipsCol"=>$x[$colDefn["tipsCol"]],
										"targetCol"=>$x[$colDefn["targetCol"]],
									);
							$as=$this->createTag($format[3],$s1,$y,$showID);
							$s.="<{$format[1]}>$as</{$format[1]}>";
						}
					} elseif(strlen($data[$colDefn["linkCol"]])>1) {
						$as=$this->createTag($format[3],$a,$colData,$showID);
						$s.="<{$format[1]} $tips >$as</{$format[1]}>";
					} else {
						//$as=createTag($format[2],$a,$colData,$showID);
						//$s.="<{$format[1]} rel='{$b['id']}'>$a</{$format[1]}>";
					}
				}
			} else {
			}
		}
		if(strlen($s)<0) return "";
		else {
			if($depth==0) return "<{$format[0]}>$s</{$format[0]}>";
			else return "<{$format[0]} class='dropdown-menu'>$s</{$format[0]}>";
		}
	}

	//This function creates the final tags to be put into the holder spaces.
	//It Needs the tag mostly li,h2,a,span
	public function createTag($tag, $title, $menuData=array(),$showID=false) {
		$colDefn=array(
					"idCol"=>"rel=\"%s\"",
					"linkCol"=>"href=\"%s\"",
					"iconCol"=>"%s",
					"classCol"=>"class='%s'",
					"styleCol"=>"style='%s'",
					"tipsCol"=>"title=\"%s\"",
					"targetCol"=>"target='%s'",
					"srcCol"=>"src='%s'",
				);
		if(!$showID) {
			unset($colDefn['idCol']);
		}
		foreach($colDefn as $a=>$b) {
			if(isset($menuData[$a]) && strlen($menuData[$a])>0) {
				$colDefn[$a]=sprintf($b,$menuData[$a]);
			} else {
				unset($colDefn[$a]);
			}
		}
		//printArray($menuData);echo $showID;
		if(isset($colDefn['tipsCol']) && strlen($colDefn['tipsCol'])<=8) {
			$colDefn['tipsCol']="title=\"$title\"";
		}
		$colDefn['tipsCol']="alias=\"$title\"";
		$icn="";
		if(isset($colDefn["iconCol"]) && strlen($colDefn["iconCol"])>1) {
			if(function_exists("findMedia")) {
				$img=findMedia($colDefn['iconCol']);
			} else {
				$img=loadMedia($colDefn['iconCol']);
			}
			if(strlen($img)>0)
				$icn="<img src='$img' />";// width=20px height=20px alt='' style='margin:0px;margin-right:10px;float:left;'
			else
				$icn="";
			unset($colDefn["iconCol"]);
		}
		if(!$this->printTitle) {
			$title="";
		} else {
			$title=_ling($title,true);
		}
		$s="<{$tag} ".implode(" ",$colDefn).">{$icn}<span>{$title}</span></{$tag}>";
		return $s;
	}

	public static function getColumnTagsHolder() {
		return array(
					"idCol"=>"id",
					"titleCol"=>"title",
					"groupCol"=>"menugroup",
					"categoryCol"=>"category",

					"linkCol"=>"link",

					"iconCol"=>"iconpath",
					"classCol"=>"class",
					"styleCol"=>"style",

					"tipsCol"=>"tips",
					"targetCol"=>"target",

					"srcCol"=>"src",
				);
	}
}
?>
