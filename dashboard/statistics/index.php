<?php
	class Statistic {
		var $fileContent = "";
		var $data = array();
		var $isUniqueIP = false;
		var $isUniqueDay = false;
		var $showWithIP = false;
		var $showHitNumber = true;
		var $sortNumber = true;
		
		function Statistic ($filepath) {			
			if (file_exists($filepath)) {
				$this->fileContent = file_get_contents($filepath);
			}
			else {
				print "File not exists";
				exit();
			}
		}
		
		function setArray($params) {
			if (!is_array($params)) {
				$params = array($params);
			}
			
			return $params;
		}
		
		function setData ($params) {
			if (!empty($this->fileContent)) {
				$params = $this->setArray($params);
				$this->data = array();
				
				foreach ($params as $param) {
					$param = json_encode($param);
					$pattern = preg_quote($param, "/");
					$pattern = trim($pattern,'"');
					$pattern = "/^.*$pattern.*\$/m";
									
					if(preg_match_all($pattern, $this->fileContent, $matches)){
					   $this->data = array_merge($this->data, $matches[0]);
					}
				}
			}
		}
		
		
		function getDataByParam ($params = false) {
			if (!$params || empty($params)) {
				return false;
			}
			
			$out = array();
			$params = $this->setArray($params);
			$this->setData($params);
			
			if (!empty($this->data)) {
				
				$unique = array();
				
				foreach ($this->data as $foo) {
					$foo = (array) json_decode($foo);
					
					if (isset($foo["path"])) {
						
						// loop parameters
						foreach ($params as $param) {
							$path = strtolower($foo["path"]);
							$search = strtolower($param);
							$parts = parse_url($foo["path"]);
							
							// check if path contain search param
							if (!empty($search) && strpos($path, $search) !== false && isset($parts['query'])) {
								
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
								$query[$param] = trim(strtolower(utf8_decode($query[$param])));
								
								if ($this->showHitNumber) {
									// check if show with IP
									if ($this->showWithIP) {
										if (!isset($out[$query[$param]][$foo["ip"]])) {
											$out[$query[$param]][$foo["ip"]] = 0;
										}
										
										$out[$query[$param]][$foo["ip"]] += 1;
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
										$out[$query[$param]][$foo["ip"]][] = $foo;
									}
									else {
										$out[$query[$param]][] = $foo;
									}
								}
								
								
								// set unique data
								if (!isset($unique[$foo["ip"]])) {
									$unique[$foo["ip"]] = array();
								}
								
								$unique[$foo["ip"]][$date] = 1;
							}	
						}
						
					}
				}
				
			}
			
			// sort data
			if (!empty($out)) {
				if ($this->sortNumber) {
					arsort($out);
				}
				else {
					ksort($out);
				}
			}
			
			return $out;
		}
		
		
		function getDataByURI ($params) {
			if (!$params || empty($params)) {
				return false;
			}
			
			$out = array();
			$params = $this->setArray($params);
			$this->setData($params);
			
			if (!empty($this->data)) {
				
				$unique = array();
				
				foreach ($this->data as $foo) {
					$foo = (array) json_decode($foo);
					
					if (isset($foo["path"])) {
						
						// loop parameters
						foreach ($params as $param) {
							$path = strtolower($foo["path"]);
							$search = strtolower($param);
							
							// check if path contain search param
							if (!empty($search) && strpos($path, $search) !== false) {
								
								// check if IP or day is unique
								$date = date("Y-m-d",strtotime($foo["datetime"]));
								if ($this->isUniqueIP && $this->isUniqueDay && isset($unique[$foo["ip"]][$date])) {
									continue;
								}
								else if ($this->isUniqueIP && isset($unique[$foo["ip"]])) {
									continue;
								}
								
								
								// build data
								$param = trim(strtolower(utf8_decode($param)));
								
								if ($this->showHitNumber) {
									// check if show with IP
									if ($this->showWithIP) {
										if (!isset($out[$param][$foo["ip"]])) {
											$out[$param][$foo["ip"]] = 0;
										}
										
										$out[$param][$foo["ip"]] += 1;
									}
									else {
										if (!isset($out[$param])) {
											$out[$param] = 0;
										}
										
										$out[$param] += 1;
									}
								}
								else {
									if ($this->showWithIP) {
										$out[$param][$foo["ip"]][] = $foo;
									}
									else {
										$out[$param][] = $foo;
									}
								}
								
								
								// set unique data
								if (!isset($unique[$foo["ip"]])) {
									$unique[$foo["ip"]] = array();
								}
								
								$unique[$foo["ip"]][$date] = 1;
							}
						}
						
					}
				}
			}
			
			// sort data
			if (!empty($out)) {
				if ($this->sortNumber) {
					arsort($out);
				}
				else {
					ksort($out);
				}
			}
			
			return $out;
		}
		
		
		function getDataFlow ($params, $limitStart = 1, $limitEnd = 5) {
			if (!$params || empty($params)) {
				return false;
			}
			
			$out = array();
			$params = $this->setArray($params);
			
			if (count($params) == 1) {
				$this->setData($params);
			
				if (!empty($this->data)) {
					
					$lookupIps = array();
					
					foreach ($this->data as $foo) {
						$foo = (array) json_decode($foo);
						
						if (isset($foo["path"])) {
							
							foreach ($params as $param) {
								$path = strtolower($foo["path"]);
								$search = strtolower($param);
								
								if (!empty($search) && strpos($path, $search) !== false) {
									$lookupIps[$foo["ip"]] = $foo["ip"];
								}
							}
						}
					}
					
					// search flow per IP
					if (!empty($lookupIps)) {
						$i = 1;
						foreach ($lookupIps as $IP) {
							
							if ($i >= $limitStart) {
							
								$this->setData($IP);
								
								if (!empty($this->data)) {
									foreach ($this->data as $foo) {
										$foo = (array) json_decode($foo);
						
										if (isset($foo["path"])) {
											$out[$IP][] = $foo["path"];
										}
									}
								}
							
							}
							
							if ($i == $limitEnd) {
								break;
							}
							
							$i++;
						}
					}
				}	
			}
			
			return $out;
		}
	}
	
	
	
	
	
	$stats = new Statistic("/home/siteloom/htdocs/theis/siteloomlog/2017-50_log.txt");
	//$stats = new Statistic("2017-50_log.txt");
	
	// set sort type
	$sortNumber = false;
	if (isset($_REQUEST["sort_number"]) && $_REQUEST["sort_number"] == "true") {
		$sortNumber = true;
	}
	
	$stats->sortNumber = $sortNumber;
	
	// set search type	
	if (isset($_REQUEST["searchparam"])) {
		$search = $_REQUEST["searchparam"];
		
		if (!empty($search)) {
			$search = explode(" ",$search);
		}
		
		$data = $stats->getDataByParam($search);
	}
	
	else if (isset($_REQUEST["searchuri"])) {
		$search = $_REQUEST["searchuri"];
		
		if (!empty($search)) {
			$search = explode(" ",$search);
		}
		
		$data = $stats->getDataByURI($search);
	}
	
	else if (isset($_REQUEST["searchflow"])) {
		$search = $_REQUEST["searchflow"];
		
		if (!empty($search)) {
			$search = explode(" ",$search);
		}
		
		$data = $stats->getDataFlow($search,1,10);
	}
	
	
	
	
	if (isset($_REQUEST["keyparam"])) {
		$key = $_REQUEST["keyparam"];
		if(isset($data[$key])){
			echo "<pre>";
			print_r($data[$key]);
			echo "</pre>";
		}
	}
	else {
		echo "<pre>";
		print_r($data);
		echo "</pre>";
		
		print "All search terms: ".sizeof($data);
		print "<br>";
		print "Total search terms: ".array_sum($data);
	}
?>