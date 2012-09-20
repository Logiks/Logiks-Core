<?php
if(!defined('ROOT')) exit('No direct script access allowed');

include_once "libs/xml2array.inc";
//DataCnversion Is Used To Convert Data From Given Source/Input/Form To Required O/P Form
//Mainly Used for Creating Components
//Supported Formats :: Array/XML/JSON/CSV/FormFormat //SQL/dbTbl-#DataID/
class DataConversion {
	private $data="";
	private $format="";
	private $arrData=null;
	
	public function __construct() {		
	}
	
	public function loadData($data) {
		$x=$this->findFormat($data);
		$this->setData($data,$x);
		//print_r($data);
		return $x;
	}
	public function setData($data,$format) {
		$this->data=$data;
		$this->format=strToLower($format);
		if($this->format=="xml") {
			$obj = new xml2array($data);
			$this->arrData=$obj->getResult();			
			return true;
		} elseif($this->format=="json") {
			$this->arrData=json_decode($data,true);
			return true;
		} elseif($this->format=="array") {
			$this->arrData=$data;
			return true;
		} elseif($this->format=="csv") {
			//CSV -> Array
			$header = null; 
			$Data=array();
			$prepareData=array();
			$this->arrData=explode("\n",$this->data);
			foreach($this->arrData as $a){
				if(is_null($header)) $header = explode(",",$a);				
				else{
					$items=explode(",",$a);
					for($n = 0, $m = count($header); $n < $m; $n++){ 
						$prepareData[$header[$n]] = $items[$n]; 
					} 
					$Data[] = $prepareData; 
				  }
			} 
			$this->arrData=$Data;
			//print_r($this->arrData);
			return true;
		} elseif($this->format=="form") {
			//FORM -> Array
			$x=explode(",",$data);
			foreach($x as $a => $b){
				$y=explode("=",$b);
				$this->arrData[$y[0]]=$y[1];
				//$b=str_replace("=","=>",$b);
			}
			return true;
		} else {
			return false;
		}
	}
	
	public function getData($format="") {
		$array=$this->arrData;
		if($format=="") {// || $format==$this->format
			return $this->data;
		} else {
			if($format=="xml") {
				$xml=new SimpleXMLElement('<root/>');
				foreach($array as $a=>$b) {
					$node=$xml->addChild("record");
					array_walk_recursive($b, "DataConversion::addNode",$node);			
				}
				$s=$xml->asXML();
				return $s;
			} elseif($format=="json") {
				return json_encode($array);
			} elseif($format=="array") {
				return $array;
			} elseif($format=="csv") {
				//ARRAY->CSV
				$csv="";
				$header=null;
				foreach($array as $key1 => $val1){
					foreach($val1 as $key => $val) {
						if(is_null($header)) $header = implode(",",array_keys($val1));	
												 					
						$csv .= $val.","; 												
					}
					//$header = substr($header, 0, -1);
					$csv = substr($csv, 0, -1); //chop last ,
					$csv .= "\n";
					
				}
				//echo $header;
				$header .= "\n";
				$csv=$header.$csv;
				//echo $csv;
				return $csv;
			} elseif($format=="form") {
				//ARRAY->FORM
				$s="";
				foreach($array as $a=>$b) {
					$s.="$a=$b,";
				}
				return $s;
			} else {
				return null;
			}			
		}
	}
	public function getFormat() {
		return $this->format;
	}
	
	protected function findFormat($data) {
		//$csv= str_getcsv($data,",");
		//print_r($csv);
		if(is_array($data)){
			return "ARRAY";
		}elseif(($xml=simplexml_load_string($data)) == true){
			return "XML";
		}elseif(($json=json_decode($data)) != NULL){
			return "JSON";
		}elseif(count($csv = str_getcsv($data,","))>0){
			$arr=explode(",",$data);
			$arr1=explode("=",$arr[0]);
			if(sizeOf($arr1)==2) {
				return "FORM";
			} else {
				return "CSV";
			}
		}else {
			return null;
		}		
	}
	
	private function addNode($item, $key, $xml) {
		$xml->addChild($key, $item);
	}
}
/*
///////////////////////////data///////////////////////////
$csv_string="a,b,c,d\ne,f,g,h\ni,j,k,l";
$form_Data=<<<FORM
a=b,c=d,e=,f=g
FORM;
/////////////////////////////////////////////////////////
$dc=new DataConversion();
$dc->loadData($csv_string);
$arr=$dc->getData("xml");
print_r($arr);
$dc->loadData($arr);
$arr=$dc->getData("csv");
print_r($arr);
$str=file_get_contents('data/test.csv');
*/
?>
