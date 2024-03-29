<?php
/*
 * This file contains the source for generating the fields ui.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("generateSelectOptions")) {

	function generateSelectOptions($fieldinfo,$data,$dbKey="app") {
		$html="";

		switch ($fieldinfo['type']) {
			case 'select':
				if(!isset($fieldinfo['options'])) $fieldinfo['options']=[];

				foreach ($fieldinfo['options'] as $key => $value) {
					if(!$value) continue;
					if(is_array($value)) {
						$cx=[];
						if(isset($value['label'])) {
							$vx=$value['label'];
							unset($value['label']);
							foreach ($value as $key => $value) {
								$cx[]="$key='$value";
							}
						} else $vx="";

						if($data==$key) {
							$html.="<option value='$key' ".implode(" ", $cx)." selected>"._ling($vx)."</option>";
						} else {
							$html.="<option value='$key' ".implode(" ", $cx).">"._ling($vx)."</option>";
						}

					} else {
						$vx=$value;
						if($data==$key) {
							$html.="<option value='$key' selected>"._ling($vx)."</option>";
						} else {
							$html.="<option value='$key'>"._ling($vx)."</option>";
						}
					}
				}

				break;
			case 'selectAJAX':
				$html.="<option value=''>Loading ...</option>";

				break;
			case 'dataMethod':
				if(isset($fieldinfo['method'])) {
					if(is_array($fieldinfo['method']) && isset($fieldinfo['method']['name'])) {
						if(isset($fieldinfo['method']['params'])) {
							if(isset($fieldinfo['method']['valuefield'])) {
								$fieldinfo['method']['params'][$fieldinfo['method']['valuefield']]=$data;
							}
							$html.=call_user_func_array($fieldinfo['method']['name'],$fieldinfo['method']['params']);
						} elseif(isset($fieldinfo['method']['valuefield'])) {
							$fieldinfo['method']['params'][$fieldinfo['method']['valuefield']]=$data;
							$html.=call_user_func_array($fieldinfo['method']['name'],$fieldinfo['method']['params']);
						} else {
							$html.=call_user_func($fieldinfo['method']['name']);
						}
					} else {
						$html.=call_user_func($fieldinfo['method']);
					}
				}

				break;
			case 'dataSelector':
				if(!isset($fieldinfo['orderBy'])) $fieldinfo['orderBy']=null;

				if(isset($fieldinfo['orderby'])) $fieldinfo['orderBy']=$fieldinfo['orderby'];
				if(isset($fieldinfo['groupby'])) $fieldinfo['groupBy']=$fieldinfo['groupby'];

				$noOption=_ling("No Selection");

				/*if(!array_key_exists("", $fieldinfo['options']) || $fieldinfo['options']['']===true) {
					$html.="<option value=''>{$noOption}</option>";
				}*/
				if(!isset($fieldinfo['groupid'])) return "";

				$html.=createDataSelector($fieldinfo['groupid'],$fieldinfo['orderBy'],$dbKey);

				break;
			case 'dataSelectorFromUniques':
				if(!isset($fieldinfo['col1'])) {
					if(isset($fieldinfo['columns'])) {
						$cols=explode(",",$fieldinfo['columns']);
						$fieldinfo['col1']=$cols[0];
						if(isset($cols[1])) $fieldinfo['col2']=$cols[1];
					}
				}
				if(!isset($fieldinfo['col2'])) $fieldinfo['col2']=$fieldinfo['col1'];
				if(!isset($fieldinfo['where'])) $fieldinfo['where']=null;
				if(!isset($fieldinfo['orderBy'])) $fieldinfo['orderBy']=null;

				if(isset($fieldinfo['orderby'])) $fieldinfo['orderBy']=$fieldinfo['orderby'];
				if(isset($fieldinfo['groupby'])) $fieldinfo['groupBy']=$fieldinfo['groupby'];

				/*if(!array_key_exists("", $fieldinfo['options']) || $fieldinfo['options']['']===true) {
					$html.="<option value=''>{$noOption}</option>";
				}*/
				if(is_array($fieldinfo['where'])) {
					foreach($fieldinfo['where'] as $a=>$b) {
						if(is_string($b)) {
							$fieldinfo['where'][$a]=_replace($b);
						}
					}
				} else {
					$fieldinfo['where']=_replace($fieldinfo['where']);
				}

				$html.=createDataSelectorFromUniques($fieldinfo['table'],$fieldinfo['col1'],$fieldinfo['col2'],$fieldinfo['where'],$fieldinfo['orderBy'],$dbKey);

				break;
			case "dataSelectorFromTable":
				if(!isset($fieldinfo['columns'])) $fieldinfo['columns']=$fieldinfo['col1'];
				if(!isset($fieldinfo['where'])) $fieldinfo['where']=null;
				if(!isset($fieldinfo['groupBy'])) $fieldinfo['groupBy']=null;
				if(!isset($fieldinfo['orderBy'])) $fieldinfo['orderBy']=null;

				if(isset($fieldinfo['orderby'])) $fieldinfo['orderBy']=$fieldinfo['orderby'];
				if(isset($fieldinfo['groupby'])) $fieldinfo['groupBy']=$fieldinfo['groupby'];

				/*if(!array_key_exists("", $fieldinfo['options']) || $fieldinfo['options']['']===true) {
					$html.="<option value=''>{$noOption}</option>";
				}*/
				if(is_array($fieldinfo['where'])) {
					foreach($fieldinfo['where'] as $a=>$b) {
						if(is_string($b)) {
							$fieldinfo['where'][$a]=_replace($b);
						}
					}
				} else {
					$fieldinfo['where']=_replace($fieldinfo['where']);
				}

				$html.=createDataSelectorFromTable($fieldinfo['table'],$fieldinfo['columns'], $fieldinfo['where'],$fieldinfo['groupBy'],$fieldinfo['orderBy'],$dbKey);

				break;
		}

		return $html;
	}
}
?>
