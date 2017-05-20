<?php
/*
 * UIComponents Core file
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("explodeTagData")) {

	function flatternArray(&$inputArray, $tmp = null, $name = '') {
	    if ($tmp === null) {
	        $tmp = $inputArray;
	    }
	    foreach($tmp as $index => $value) {
	        if (is_array($value)) {
	            flatternArray($inputArray, $value, $name.'_'.$index);

	            if (isset($inputArray[$index])) {
	                unset($inputArray[$index]);
	            }
	        } else {
	            $inputArray[$name.'_'.$index] = $value;
	            //$inputArray[$index] = $value;
	        }
	    }
	    foreach ($inputArray as $m=>$n) {
			if(substr($m, 0,1)=="_") {
				unset($inputArray[$m]);
				$inputArray[substr($m, 1)]=$n;
			}
		}
	    return $inputArray;
	}

	//Value Format
	function explodeTagData($value) {
		$s=$value;
		$arr = array("value"=>"","id"=>"","class"=>"");
		$id="";
		$cls="";

		$value=explode('@',$value);
		if(isset($value[1])) {
			$cls =$value[1];
			if(strpos($cls,'#')>0){
				$cls=explode('#',$cls);

				if(isset($cls[1]))
					$id =$cls[1];
				else $id="";
				$cls=$cls[0];
			}
		}

		if(strpos($value[0],'#')>0 || strpos($s,'#')==0){
			$value=explode('#',$value[0]);
			if(isset($value[1])) $id .=$value[1];
		}

		$arr['value']=$value[0];
		$arr['id']=$id;
		$arr['class']=$cls;

		return $arr;
	}

	function parseTagParams($params) {
		$s="";
		foreach($params as $a=>$b) {
			if(strlen($b)>0)
				$s.=" $a='$b'";
		}
		return trim($s);
	}

	function parseFieldParams($params=array()) {
		$s="";

		$cls="";
		$val="";
		$id="";
		$name="";
		$tips="";
		$rel="";
		$type="";
		$attrs="";

		if(!isset($params['type'])) $params['type']="text";

		if(isset($params['class'])) $cls=$params['class'];
		if(isset($params['val'])) $cls=$params['value'];
		if(isset($params['tips'])) $cls=$params['tips'];
		if(isset($params['rel'])) $cls=$params['rel'];
		if(isset($params['name'])) $cls=$params['name'];
		if(isset($params['id'])) $cls=$params['id'];
		if(isset($params['attrs'])) $attrs=$params['attrs'];

		if(strlen($name)<=0) $name=$id;

		if($params['type']=="text") {
			$type="text";
			$cls.=" textfield";
		}
		elseif($params['type']=="email") {
			$type="text";
			$cls.=" emailfield";
		} elseif($params['type']=="phone") {
			$type="text";
			$cls.=" phonefield";
		} elseif($params['type']=="mobile") {
			$type="text";
			$cls.=" mobilefield";
		} elseif($params['type']=="creditcard") {
			$type="text";
			$cls.=" creditcardfield";
		} elseif($params['type']=="currency") {
			$type="text";
			$cls.=" currencyfield";
		} elseif($params['type']=="barcode") {
			$type="text";
			$cls.=" barcodefield";
		} elseif($params['type']=="url") {
			$type="text";
			$cls.=" urlfield";
		} elseif($params['type']=="tag") {
			$type="text";
			$cls.=" tagfield";
		}
		elseif($params['type']=="file") {
			$type="file";
			$cls.=" filefield";
		}
		elseif($params['type']=="date") {
			$type="date";
			$cls.=" datefield";
		} elseif($params['type']=="time") {
			$type="time";
			$cls.=" timefield";
		} elseif($params['type']=="datetime") {
			$type="date";
			$cls.=" datetimefield";
		}
		elseif($params['type']=="textarea") {
			$s="<textarea";
			if(strlen($id)>0) $s.="name='$id' id='$id' ";
			if(strlen($cls)>0) $s.="class='".trim($cls)."' ";
			if(strlen($val)>0) $s.="value='$val' ";
			if(strlen($rel)>0) $s.="rel='$rel' ";
			if(strlen($tips)>0) $s.="title='$tips' ";
			$s.="$attrs >";
			$s.="</textarea>";
		}
		elseif($params['type']=="pwd" || $params['type']=="password") {
			$type="password";
			$cls.=" passwordfield";
		}
		elseif($params['type']=="select") {
			$s="<select";
			if(strlen($id)>0) $s.="name='$id' id='$id' ";
			if(strlen($cls)>0) $s.="class='".trim($cls)."' ";
			if(strlen($val)>0) $s.="value='$val' ";
			if(strlen($rel)>0) $s.="rel='$rel' ";
			if(strlen($tips)>0) $s.="title='$tips' ";
			$s.="$attrs >";

			if(isset($params['options'])) {
				if($params['options']=="func" || $params['options']=="function") {
					if(isset($params['func']) && function_exists($params['func'])) {
						$xr=call_user_func($params['func']);
						if(is_array($xr)) {
							foreach($xr as $a1=>$b1) {
								$s.="<option value=$a1>$b1</option>";
							}
						} else {
							$s.=$xr;
						}
					}
				} else {
					$s.=$params['options'];
				}
			}

			$s.="</select>";
		} else {
			$type="text";
		}
		if(strlen($s)<=0) {
			$s="<input ";
			if(strlen($type)>0) $s.="type='$type' ";
			if(strlen($id)>0) $s.="name='$id' id='$id' ";
			if(strlen($cls)>0) $s.="class='".trim($cls)."' ";
			if(strlen($val)>0) $s.="value='$val' ";
			if(strlen($rel)>0) $s.="rel='$rel' ";
			if(strlen($tips)>0) $s.="title='$tips' ";
			$s.="$attrs />";
		}
		return $s;
	}
	function parseFieldHelp($params=array()) {
		$s="";

		return $s;
	}
	function createPaginationArray($max, $page, $limit) {
		$next=$page+1;$prev=$page-1;
		if($next*$limit>$max) $next=$page;
		if($prev<=0) $prev=0;

		$data=[
				"max"=>$max,
				"page"=>$page,
				"page_next"=>$next,
				"page_prev"=>$prev,
				"page_max"=>ceil($max/$limit),
				"limit"=>$limit,
				"index"=>$page*$limit
			 ];

	 return $data;
  }
}
?>
