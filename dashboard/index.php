<?php
	class Statistic {
		var $filepath;
		var $data = array();
		var $isUniqueIP = false;
		var $isUniqueDay = false;
		var $showWithIP = true;
		var $showHitNumber = true;
		
		function Statistic ($filepath) {
			$this->filepath = $filepath;			
		}
		
		function setData ($query) {
			$contents = file_get_contents($this->filepath);
			$pattern = preg_quote($query, '/');
			$pattern = "/^.*$pattern.*\$/m";
			
			if(preg_match_all($pattern, $contents, $matches)){
			   $this->data = $matches[0];
			}
		}
		
		function getData(){
			$contents = file_get_contents($this->filepath);
			$contents = explode("\n", $contents);
			$contents = array_filter($contents);
			foreach($contents as $content){
				$data[] = (array) json_decode($content);
			}
			
			return $data;
		}
		
		function getDataByParam ($param = false) {
			if (!$param) {
				return false;
			}
			
			$this->setData($param);
			
			$out = array();
			
			if (!empty($this->data)) {
				
				$unique = array();
				
				foreach ($this->data as $foo) {
					$foo = (array) json_decode($foo);
					
					if (isset($foo["path"])) {
						$path = strtolower($foo["path"]);
						$search = strtolower($param);
						$parts = parse_url($foo["path"]);
						
						// check if path contain search param
						if (strpos($path, $search) !== false && isset($parts['query'])) {
							
							// check if IP or day is unique
							$date = date("Y-m-d",strtotime($foo["datetime"]));
							if ($this->isUniqueIP && $this->isUniqueDay && isset($unique[$foo["ip"]][$date])) {
								continue;
							}
							else if ($this->isUniqueIP && isset($unique[$foo["ip"]])) {
								continue;
							}
							
							
							// build data
							parse_str($parts['query'], $query);
							
							if ($this->showHitNumber) {
								// check if show with IP
								if ($this->showWithIP) {
									if (!isset($out[$query[$param]][$foo["ip"]])) {
										$out[strtolower($query[$param])][$foo["ip"]] = 0;
									}
									
									$out[strtolower($query[$param])][$foo["ip"]] += 1;
								}
								else {
									if (!isset($out[$query[$param]])) {
										$out[$query[$param]] = 0;
									}
									
									$out[$query[$param]] += 1;
								}
							}
							else {
								if ($this->showWithIP) {
									$out[strtolower($query[$param])][$foo["ip"]][] = $foo;
								}
								else {
									$out[strtolower($query[$param])][] = $foo;
								}
							}
							
							
							// set unique data
							if (isset($unique[$foo["ip"]])) {
								$unique[$foo["ip"]][$date] = 1;
							}
							else {
								$unique[$foo["ip"]] = array();
							}
						}
					}
				}
				
				
			}
			
			ksort($out);
			return $out;
		}
		
		function getDataByURI () {
			
		}
	}
	
	$search = false;	
	if (isset($_REQUEST["searchparam"])) {
		$search = $_REQUEST["searchparam"];
	}
	$logFile = "logs-".date("y-m");
	$filepath = $_SERVER["DOCUMENT_ROOT"].'/files/design/php/shopify/dashboard/logs/'. $logFile . ".log";
	$stats = new Statistic($filepath);
	$data = $stats->getData();

	echo "<pre>";
	print_r($data);
	echo "</pre>";
		
	
	
	
	
	
?>