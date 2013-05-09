<?php
class xml2array {
	function xml2array($xml) {
		if(is_file($xml)) {
			//get xml string from xmlfile
			$xml = file_get_contents($xml);
			$this->dom = new DOMDocument;
			$this->dom->loadXml($xml);
		} elseif(is_string($xml)) {
			$this->dom = new DOMDocument;
			$this->dom->loadXml($xml);
		}

		return FALSE;
	}

	function _process($node) {
		$occurance = array();

		foreach($node->childNodes as $child) {
			$occurance[$child->nodeName]++;
		}

		if($node->nodeType == XML_TEXT_NODE) {
			$result = html_entity_decode(htmlentities($node->nodeValue, ENT_COMPAT, 'UTF-8'),
                                     ENT_COMPAT,'ISO-8859-15');
		}
		else {
			if($node->hasChildNodes()){
				$children = $node->childNodes;

				for($i=0; $i<$children->length; $i++) {
					$child = $children->item($i);

					if($child->nodeName != '#text') {
						if($occurance[$child->nodeName] > 1) {
							$result[$child->nodeName][] = $this->_process($child);
						}
						else {
							$result[$child->nodeName] = $this->_process($child);
						}
					}
					else if ($child->nodeName == '#text') {
						$text = $this->_process($child);

						if (trim($text) != '') {
							$result[$child->nodeName] = $this->_process($child);
						}
					}
				}
			}

			if($node->hasAttributes()) {
				$attributes = $node->attributes;

				if(!is_null($attributes)) {
					foreach ($attributes as $key => $attr) {
						$result["@".$attr->name] = $attr->value;
					}
				}
			}
		}

		return $result;
	}

	function getResult() {
		//print_r($this->_process($this->dom));
		return $this->_process($this->dom);
	}
}
?>
