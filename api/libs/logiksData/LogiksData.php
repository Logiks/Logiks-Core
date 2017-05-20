<?php
/*
 * Logiks Processed Data Result container. This contains the query results for future references.
 * 
 * Author: Bismay Kumar Mohapatra (bismay@openlogiks.org)
 * Version: 2.0
 */

class LogiksData {

	private $data=[];
	private $key="";

	private $resultSet=[];
	private $temp="";

	public function __construct($data,$key) {
		$this->key=$key;
		$this->data=$data;
 	}
 	//Use array map to find data in nested arrays
 	// public function query() {

 	// }

 	public function mapFirstRowToColumnNames() {
 		$cols=$this->data[0];

 		foreach($this->data as $a=>$b) {
 			foreach ($b as $m => $n) {
 				unset($this->data[$a][$m]);
 				$this->data[$a][$cols[$m]]=$n;
 			}
 		}
 		unset($this->data[0]);
 		return $this;
 	}

 	//Clean all old search history
 	public function purge($q=null) {
 		if($q==null) {
 			$this->resultSet=[];
 		} else {
 			$this->resultSet[$q]=[];
 		}
 	}
 	public function search($q) {
 		//if(isset($this->resultSet[$q])) return $this->resultSet[$q];
 		$this->resultSet[$q]=[];
 		$this->temp=$q;
 		//printArray($this->data);
 		array_walk($this->data, array($this, 'searchInArray'));
 		return $this->resultSet[$q];
 	}
 	public function dump() {
 		return $this->data;
 	}


 	//TODO : Nested Array Search
 	protected function searchInArray($item, $key) {
 		if(is_array($item)) {
 			foreach ($item as $a => $b) {
 				if(is_array($b)) continue;
 				if(strpos($a, $this->temp)===0 || strpos($a, $this->temp)>0) {
		 			$this->resultSet[$this->temp][$key]=$item;
		 		} elseif(strpos($b, $this->temp)===0 || strpos($b, $this->temp)>0) {
		 			$this->resultSet[$this->temp][$key]=$item;
		 		}	
 			}
 			return false;
 		} else {
 			if(strpos($item, $this->temp)===0 || strpos($item, $this->temp)>0) {
	 			$this->resultSet[$this->temp][$key]=$this->data[$key];
	 		} elseif(strpos($key, $this->temp)===0 || strpos($key, $this->temp)>0) {
	 			$this->resultSet[$this->temp][$key]=$this->data[$key];
	 		}	
 		}
 		//return false;
 		//if(is_array($key)) return false;
 		//println("$item, $key");
 	}

 	public function __destruct() {
 		$this->data=null;
 		$this->key=null;
 	}

 	public function __debugInfo() {
        return [];
  }
}
?>