<?php
	class Statistic {
		private $excludeData = array(
			"http_user_agent" => array(
				"bot",
				"nagios-plugins"
			),
			"ip" => array(
				"77.66.90.243",
				"77.66.23.66",
				"77.66.90.241",
				"95.209.150.185"
			),
			"path" => array(
				".asp",
				".jpg",
				"wp-content",
				"robots.txt",
				".js"
			)
		);
		
		private $filePath;
		private $fileContent = "";
		private $data = array();
		private $elements;
		
		var $config = array(
			"show_hit_number" => true,
			"sort_by_number" => true,
			"exclude_data" => array()
		);
		
		var $debug = false;
		
		
		
		function Statistic ($filepath = false) {
			global $_SITELOOM;
			global $connection;
			
			require_once($_SERVER["DOCUMENT_ROOT"]."/cms/class/class.elements.php");
			
			$this->elements = new Elements($connection);
			
			if ($filepath) {
				$this->filePath = $filepath;			
				$this->loadContent();	
			}
		}
		
		function loadContent () {
			
			if (file_exists($this->filePath)) {
				$this->fileContent = file_get_contents($this->filePath);
			}
			else {
				print "File not exists";
				exit();
			}
		}
		
		function setArray($params) {
			
			if (!empty($params) && !is_array($params)) {
				$params = array($params);
			}
			
			return $params;
		}
		
		function setData ($params) {
			
			if (!empty($this->fileContent)) {
				$params = $this->setArray($params);
				$this->data = array();
				
				$pattern = "";
				
				foreach ($params as $param) {
					$param = json_encode($param);
					$param = preg_quote($param, "/");
					$param = trim($param,'"');
					
					
					if (!empty($pattern)) {
						$pattern .= "|";
					}
					
					$pattern .= $param;
				}
				
			
				if (!empty($pattern)) {
					$pattern = "/^.*(".$pattern.").*\$/m";				
					if(preg_match_all($pattern, $this->fileContent, $matches)){
						$this->data = $matches[0];
					}
				}
			}
						
		}
		
		function isExcludeData ($data) {
			$out = false;
			
			// merge exclude data from config
			if (!empty($this->config["exclude_data"])) {
				$this->excludeData = array_merge_recursive($this->excludeData, $this->config["exclude_data"]);
				$this->config["exclude_data"] = array();
			}
			
			if (!empty($this->excludeData)) {
				foreach ($this->excludeData as $type => $value) {
					if (isset($data[$type])) {
						if (!empty($value) && is_array($value)) {
							foreach ($value as $foo) {
								if ($type == "ip") {
									if ($foo == $data[$type]) {
										$out = true;
									}
								}
								else {
									if (strpos($data[$type], $foo) !== false) {
										$out = true;
									}
								}
							}
						}	
					}
				}
			}
			
			return $out;
		}
		
		
		
		
		function getDataByParam ($params = false) {
			if (!$params || empty($params)) {
				return false;
			}
			
			$out = array();
			$params = $this->setArray($params);
			$this->setData($params);
			
			if (!empty($this->data)) {
				
				foreach ($this->data as $foo) {
					$foo = (array) json_decode($foo);
					
					// check exclude data
					if ($this->isExcludeData($foo)) {
						continue;
					}
					
					if (isset($foo["path"])) {
						
						// loop parameters
						foreach ($params as $param) {
							$path = strtolower($foo["path"]);
							$search = strtolower($param);
							$parts = parse_url($foo["path"]);
							
							// check if path contain search param
							if (!empty($search) && strpos($path, $search) !== false && isset($parts['query'])) {							
								
								// build data
								parse_str($parts['query'], $query);
								$query[$param] = trim(strtolower(utf8_decode($query[$param])));
								
								if ($this->config["show_hit_number"]) {
									if (!isset($out[$query[$param]])) {
										$out[$query[$param]] = 0;
									}
									
									$out[$query[$param]] += 1;
								}
								else {
									$out[$query[$param]][] = $foo;
								}
							}	
						}
						
					}
				}
				
			}
			
			// sort data
			if (!empty($out)) {
				if ($this->config["sort_by_number"]) {
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
				
				foreach ($this->data as $foo) {
					$foo = (array) json_decode($foo);
					
					// check exclude data
					if ($this->isExcludeData($foo)) {
						continue;
					}
					
					if (isset($foo["path"])) {
						
						// loop parameters
						foreach ($params as $param) {
							$path = strtolower($foo["path"]);
							$search = strtolower($param);
							
							// check if path contain search param
							if (!empty($search) && strpos($path, $search) !== false) {
								
								// build data
								$param = trim(strtolower(utf8_decode($param)));
								
								if ($this->config["show_hit_number"]) {
									if (!isset($out[$param])) {
										$out[$param] = 0;
									}
									
									$out[$param] += 1;
								}
								else {
									$out[$param][] = $foo;
								}
							}
						}
						
					}
				}
			}
			
			// sort data
			if (!empty($out)) {
				if ($this->config["sort_by_number"]) {
					arsort($out);
				}
				else {
					ksort($out);
				}
			}
			
			return $out;
		}
		
		
		function getDataFlow ($params) {
			if (!$params || empty($params)) {
				return false;
			}
			
			if (empty($this->filePath)) {
				// find lastest week log file
				$today = date("Y-m-d");
				$date = new DateTime($today);
				$week = $date->format("W");
				$year = date("y");
				
				$this->filePath = $_SERVER["DOCUMENT_ROOT"]."files/design/php/shopify/dashboard/logs/logs-".$year."-".$week.".log";
				$this->loadContent();
			}
			
			$out = array();
			$params = $this->setArray($params);
			
			if ($this->debug) {
				$agent = "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Safari/537.36";
				$browser = $this->getBrowser($agent);
				print_r($browser);
			}
			
			
			$this->setData($params);
		
			if (!empty($this->data)) {
				
				$lookupMemberIds = array();
				
				foreach ($this->data as $foo) {
					$foo = (array) json_decode($foo);
					
					// check exclude data
					if ($this->isExcludeData($foo)) {
						continue;
					}
					
					// browser
					if (isset($foo["http_user_agent"])) {
						$browser = $this->getBrowser($foo["http_user_agent"]);
						$foo["browser_icon"] = $browser["icon"];
						$foo["browser"] = $browser["name"];
						
						if (!empty($browser["version"])) {
							$foo["browser"] .= " v. ".$browser["version"];
						}
					}
					
					$out[] = $foo;
					
					if (!empty($foo["memberid"])) {
						$lookupMemberIds[$foo["session_id"]] = $foo["memberid"];
					}
				}
				
				
				// basket products
				if (!empty($out)) {
					$productIds = array();
					
					foreach ($out as $key => $flow) {
						if (isset($flow["cart"])) {
							if (isset($flow["cart"]) && !empty($flow["cart"]) && $flow["path"] == "/cart") {
								$flow["cart"] = json_decode($flow["cart"]);
								if($flow["cart"]->item_count >= 1) {
									foreach ($flow["cart"]->items as $key2 => $product) {
										$product = (array) $product;
										$productIds[$product["id"]] = $product["id"];
										$out[$key]["basket"][$key2] = $product;
									}
								}
								
							}
						}
					}
					
				}
				
				
				if (!empty($out) && !empty($lookupMemberIds)) {
					foreach($lookupMemberIds as $k => $lookupMemberId){
						$lookupMemberId = preg_replace('/[^0-9]+/', '', $lookupMemberId);
						$orders[$k] = $this->get_order($lookupMemberId);
					}
					
					
					
					foreach ($orders as $_key => $order) {
						if(isset($order["orders"])){
							foreach($order["orders"] as $key => $foo){
								$_order[$foo["id"]] = $foo;
							}
							foreach($order["orders"] as $key => $foo){
								$customers[$foo["customer"]["id"]] = $foo["customer"];
								$customers[$foo["customer"]["id"]]["ordersCount"] = $foo["customer"]["orders_count"];
								$customers[$foo["customer"]["id"]]["totalSpent"] = $foo["customer"]["total_spent"];
								$customers[$foo["customer"]["id"]]["_last_order_date"] = $_order[$foo["customer"]["last_order_id"]]["created_at"];
							}
						}

					}
					
					
					
					
					$lookup = array();
					foreach ($out as $key => $foo) {
						$foo["memberid"] = preg_replace('/[^0-9]+/', '', $foo["memberid"]);
						$foo["ordersCount"] = "0";
						$foo["totalSpent"] = "0";
						$foo["lastOrderDate"] = "";
						
						if (!empty($foo["memberid"]) && isset($customers[$foo["memberid"]])) {
							$foo["ordersCount"] = $customers[$foo["memberid"]]["ordersCount"];
							$foo["totalSpent"] = $customers[$foo["memberid"]]["totalSpent"];
							$foo["lastOrderDate"] = $customers[$foo["memberid"]]["_last_order_date"];
						}
						
						$lookup[$key] = $foo;
					}

					
					$out = $lookup; 
				}
			}	
			
			
			return $out;
		}
		
		function getDiscounts(){
			$rules = file_get_contents("https://205fb2f0675455a0f71bf0a45dd8da0f:265bc180183890d2e86645fe062e1c16@indosoft-shopify.myshopify.com/admin/price_rules.json");
			$rules = json_decode($rules , true);
			$html = array();
			if(sizeof($rules["price_rules"])){
				foreach($rules["price_rules"] as $rule){
					if($rule["value_type"] == "percentage" && $rule["target_type"] != "shipping_line"){
						$text = abs($rule["value"]) ."% off entire order";
					}
					else if( $rule["value_type"] == "fixed_amount"){
						$rule["value"] = abs($rule["value"]);
						$rule["value"] = number_format($rule["value"],2,',','.');
						$text = $rule["value"]." off entire order";
					}
					else if($rule["value_type"] == "percentage" && $rule["target_type"] == "shipping_line"){
						$text = "Free shipping on entire order";
					}
					
					$html[] =  "<a class=\"btn btn-success show-visitor-form\" data-form=\"".$rule["id"]."\">".$text."</a>";
					
					//$voucher[] = file_get_contents("https://205fb2f0675455a0f71bf0a45dd8da0f:265bc180183890d2e86645fe062e1c16@indosoft-shopify.myshopify.com/admin/price_rules/".$rule."/discount_codes.json");
				}
				
			}
			return join($html);
			
		}
		
		function get_order($id , $paid = true){
			if($paid){
				$ch = file_get_contents("https://205fb2f0675455a0f71bf0a45dd8da0f:265bc180183890d2e86645fe062e1c16@indosoft-shopify.myshopify.com/admin/customers/$id/orders.json?financial_status=paid");
			}
			else{
				$ch = file_get_contents("https://205fb2f0675455a0f71bf0a45dd8da0f:265bc180183890d2e86645fe062e1c16@indosoft-shopify.myshopify.com/admin/customers/$id/orders.json");
			
			}
			$ch = json_decode($ch , true);
			return $ch;
		}
		
		
		function getDataLive ($range = 5) {
			$out = array(
				"total_visitor" => 0,
				"search" => array(),
				"campaign" => array(),
				"path" => array(),
				"total_price" => 0
			);
			
			// find lastest week log file
			$today = date("Y-m-d");
			$date = new DateTime($today);
			$week = $date->format("W");
			$year = date("y");
			
			$this->filePath = $_SERVER["DOCUMENT_ROOT"]."files/design/php/shopify/dashboard/logs/logs-".$year."-".$week.".log";
			$this->loadContent();
			
			// build data
			$data = array();
			$params = array();
			
			
			for ($i = $range; $i >= 1; $i--) {
				$min = $i - 1;
				
				if (!$min) {
					$params[] = date("Y-m-d H:i");
				}
				else {
					$params[] = date("Y-m-d H:i", strtotime("-".$min." minutes"));
				}
			}
			
			//$params[] = date("Y-m-d");
			
			if (!empty($params)) {
				$this->setData($params);
				$data = $this->data;
			}
			
			if ($this->debug) {
				echo "<pre>";
				print_r($data);
				echo "</pre>";
			}
			
			// parse data
			if (!empty($data)) {				
				$unique = array();
				$data = array_reverse($data);
				
				foreach ($data as $foo) {
					$foo = (array) json_decode($foo);
					$foo["prices"] = json_decode($foo["prices"]);
					// check exclude data
					if ($this->isExcludeData($foo)) {
						continue;
					}
					
					if (isset($foo["path"])) {
						
						// check query path done by visitor without check unique visit
						$parts = parse_url($foo["path"]);
						if (isset($parts['query'])) {
							parse_str($parts['query'], $query);
							
							// query search
							$query["search"] = trim(strtolower(utf8_decode($query["search"])));
							$out["search"][$query["search"]] = $query["search"];
							
							// query campaign
							$query["utm_source"] = trim(strtolower(utf8_decode($query["utm_source"])));
							$query["utm_campaign"] = trim(strtolower(utf8_decode($query["utm_campaign"])));
							
							if (!empty($query["utm_source"]) || !empty($query["utm_campaign"])) {
								if (empty($query["utm_source"])) {
									$query["utm_source"] = "unknown source";
								}
								
								if (empty($query["utm_campaign"])) {
									$query["utm_campaign"] = "unknown campaign";
								}
								
								$campaign = trim($query["utm_source"].", ".$query["utm_campaign"]);
								
								if (!isset($out["campaign"][$campaign])) {
									$out["campaign"][$campaign] = 0;
								}
								
								$out["campaign"][$campaign] += 1;	
							}
						}
						
						
						
						// check unique session each user
						if (isset($unique[$foo["session_id"]])) {
							continue;
						}
						
						// path visitor
						$path = preg_replace('/\?.*/', '', $foo["path"]);
						
						if ($this->config["show_hit_number"]) {
							if (!isset($out["path"][$path])) {
								$out["path"][$path]["visitor"] = 0;
								$out["path"][$path]["price"] = 0;
							}
							
							$out["path"][$path]["visitor"] += 1;
							$out["path"][$path]["price"] += $foo["prices"]->total->productsSubtotal;
							$out["path"][$path]["session_id"][] = $foo["session_id"];
						}
						else {
							$out["path"][$path][] = $foo;
						}
						
						// total visitor
						$out["total_visitor"] += 1;
						
						// total price
						$out["total_price"] += $foo["prices"]->total->productsSubtotal;
						
						// set unique session each user
						if (!isset($unique[$foo["session_id"]])) {
							$unique[$foo["session_id"]] = 1;
						}
					}
				}
			}
			
			// sort data
			if (!empty($out["path"])) {
				$sort = array();
				$path = array();
				
				foreach ($out["path"] as $boo => $foo) {
					$sort[$boo] = $foo["visitor"];
				}
				
				arsort($sort);
				
				foreach ($sort as $boo => $foo) {
					$path[$boo] = $out["path"][$boo];
				}
				
				$out["path"] = $path;
			}
			
			return $out;
		}
		
		function getDataWeek ($year, $week, $type, $days = array()) {
			$out = array();
			
			$this->filePath = "/home/siteloom/htdocs/theis/siteloomlog/".$year."-".$week."_log.txt";
			$this->loadContent();
			
			$days = $this->setArray($days);
			$dto = new DateTime();
			$dto->setISODate($year, $week);
		
			// Days to request
			if (empty($days)) {
				$params = array();
				$params[] = $dto->format("Y-m-d");
				foreach (range(1,6) as $day) {
					$dto->modify("+1 days");
					$params[] = $dto->format("Y-m-d");
				}
			}
			else {
				$params = array();
				
				foreach ($days as $day) {
					$day = $day - 1;
					$dto2 = clone $dto;
					
					if ($day) {
						$dto2->modify("+".$day." days");
					}
					
					$params[] = $dto2->format("Y-m-d");
				}
			}
			
			$this->setData($params);
			
			if (!empty($this->data)) {
				if ($type == "checkout-flow") {
					$lookup = array();
					$path = array("/kurv/","/betalingsflow/step1/","/betalingsflow/step2/");
					
					foreach ($this->data as $foo) {
						$foo = (array) json_decode($foo);
						
						// check exclude data
						if ($this->isExcludeData($foo)) {
							continue;
						}
					
						if (isset($foo["path"])) {							
							if (in_array($foo["path"],$path)) {
								$lookup[$foo["path"]][$foo["session_id"]] = $foo["prices"]->total->productsSubtotal;
							}
							
							if ($foo["path"] == "/betalingsflow/step2/") {
								$step2[$foo["session_id"]] = $foo["prices"]->total->productsSubtotal;
							}
							
							if ($foo["path"] == "/betalingsflow/kvittering/") {
								$last[$foo["session_id"]][$foo["buy_order_id"]] = $foo["prices"]->total->productsSubtotal;
								$lastor[$foo["buy_order_id"]] = $foo["prices"]->total->productsSubtotal;
							}
						}
					}
					
					if ($this->debug) {
						print "Step 2 <br />";
						echo "<pre>";
						print_r($step2);
						echo "</pre>";
						print "Visitor: ".count($step2)."<br />";
						print "Amount: ".array_sum($step2)."<br />";
						print "=================<br />";
						
						print "Kvittering Log<br />";
						echo "<pre>";
						print_r($last);
						echo "</pre>";
						print "Visitor: 11<br />";
						print "Order: 12<br />";
						print "Amount: ".array_sum($lastor)."<br />";
						print "=================<br />";
					}
					
					// Orders
					$path[] = "/betalingsflow/kvittering/";
					
					if (!empty($params)) {
						
						$searchobject = false;
						$searchobject[] = array(
							"fieldname" => "payed",
							"searchtype" => "=",
							"value" => "true"
						);
						
						$searchobject[] = "AND (";
						
						foreach ($params as $key => $param) {
							if ($key) {
								$searchobject[] = " OR ";
							}
							
							$searchobject[] = array(
								"fieldname" => "createddate",
								"searchtype" => "LIKE",
								"value" => "%".$param."%"
							);
						}
					
						$searchobject[] = ")";
						
						$transactions = $this->elements->getElementData("sl_shop_orders_transactions",false,"*",false,false,false,$searchobject);	
		
						$orderIDs = array();
						
						if(sizeof($transactions) > 0 AND is_array($transactions)){
							foreach($transactions as $transaction){
								$orderIDs[] = $transaction["parentid"];		
							}
						}
						
						if(sizeof($orderIDs) > 0 AND is_array($orderIDs)){
							$allOrders = $this->elements->getElementData("sl_shop_orders",$orderIDs,"*");
							
							if(sizeof($allOrders) > 0 AND is_array($allOrders)){
								foreach($allOrders as $order){
									$lookup["/betalingsflow/kvittering/"][$order["ordernumber"]] = ($order["totalamount"]/100)*0.8;
									$or[$order["id"]] = ($order["totalamount"]/100)*0.8;
								}
							}
						}
						
						if ($this->debug) {
							print "Kvittering Element (sl_shop_orders)<br />";
							
							echo "<pre>";
							print_r($or);
							echo "</pre>";
							print "Order: 12<br />";
							print "Amount: ".array_sum($or)."<br />";
						}
					}
					
					// Build data array
					foreach ($path as $boo) {
						$out[$boo]["number"] = 0;
						$out[$boo]["price"] = 0;
						
						if (isset($lookup[$boo]) && !empty($lookup[$boo])) {
							foreach ($lookup[$boo] as $session => $price) {
								$out[$boo]["number"] += 1;
								$out[$boo]["price"] += $price;
							}
						}	
					}
					
					// Lost data
					foreach ($path as $key => $boo) {
						$nextPath = $path[($key + 1)];
						if (!empty($nextPath)) {
							$out[$boo]["lost_number"] = $out[$boo]["number"] - $out[$path[($key + 1)]]["number"];
							$out[$boo]["lost_price"] = $out[$boo]["price"] - $out[$path[($key + 1)]]["price"];
						}
					}
				}
				else if ($type == "search") {
					$lookup = array();
					
					foreach ($this->data as $foo) {
						$foo = (array) json_decode($foo);
						
						// check exclude data
						if ($this->isExcludeData($foo)) {
							continue;
						}
						
						$search = strtolower("search");
						$path = strtolower($foo["path"]);
						$parts = parse_url($foo["path"]);
						
						// check if path contain search param
						if (!empty($search) && strpos($path, $search) !== false && isset($parts["query"])) {							
							
							// build data
							parse_str($parts["query"], $query);
							$query[$search] = mb_convert_encoding(trim(strtolower(utf8_decode($query[$search]))), "UTF-8");
							
							if (!isset($lookup[$query[$search]][$foo["session_id"]])) {
								$lookup[$query[$search]][$foo["session_id"]] = 0;
							}
							
							$lookup[$query[$search]][$foo["session_id"]] += 1;
						}	
					}
					
					if (!empty($lookup)) {
						foreach ($lookup as $search => $foo) {
							if (!empty($foo)) {
								foreach ($foo as $boo) {
									if (!isset($out[$search])) {
										$out[$search] = 0;
									}
									
									$out[$search] += $boo;
								}
							}
						}
					}
					
					if (!empty($out)) {
						arsort($out);
					}
				}
				else if ($type == "campaigns") {
					$lookup = array();
					
					foreach ($this->data as $foo) {
						$foo = (array) json_decode($foo);
						
						// check exclude data
						if ($this->isExcludeData($foo)) {
							continue;
						}
						
						$path = strtolower($foo["path"]);
						$search = strtolower("utm_campaign");
						$parts = parse_url($foo["path"]);
						
						// check if path contain search param
						if (!empty($search) && strpos($path, $search) !== false && isset($parts["query"])) {							
							
							// build data
							parse_str($parts["query"], $query);
							$query["utm_campaign"] = trim(strtolower(utf8_decode($query["utm_campaign"])));
							
							if (!isset($lookup[$query["utm_campaign"]][$foo["session_id"]])) {
								$lookup[$query["utm_campaign"]][$foo["session_id"]] = 0;
							}
							
							$lookup[$query["utm_campaign"]][$foo["session_id"]] += 1;
						}	
					}
					
					if (!empty($lookup)) {
						foreach ($lookup as $search => $foo) {
							if (!empty($foo)) {
								foreach ($foo as $boo) {
									if (!isset($out[$search])) {
										$out[$search] = 0;
									}
									
									$out[$search] += $boo;
								}
							}
						}
					}
					
					if (!empty($out)) {
						arsort($out);
					}
				}
				else if ($type == "last-page") {
					$lookup = array();
					
					foreach ($this->data as $foo) {
						$foo = (array) json_decode($foo);
						
						// check exclude data
						if ($this->isExcludeData($foo)) {
							continue;
						}
						
						if (isset($foo["path"])) {
							$path = preg_replace("/\?.*/", "", $foo["path"]);
							$lookup[$foo["session_id"]]["path"] = $path;
							$lookup[$foo["session_id"]]["price"] = $foo["prices"]->total->productsSubtotal;
						}
					}
					
					
					if (!empty($lookup)) {
						$sort = array();
						
						foreach ($lookup as $page) {
							if (!isset($out[$page["path"]])) {
								$out[$page["path"]]["number"] = 0;
								$out[$page["path"]]["price"] = 0;
								$sort[$page["path"]] = 0;
							}
							$out[$page["path"]]["number"] += 1;
							$out[$page["path"]]["price"] += $page["price"];
							$sort[$page["path"]] += 1;
						}
						
						// sort
						arsort($sort);
						$dataSort = array();
						foreach ($sort as $path => $foo) {
							$dataSort[$path] = $out[$path];
						}
						
						$out = $dataSort;
					}
				}
				else if ($type == "start-page") {
					$lookup = array();
					
					foreach ($this->data as $foo) {
						$foo = (array) json_decode($foo);
						
						// check exclude data
						if ($this->isExcludeData($foo)) {
							continue;
						}
						
						if (isset($foo["path"])) {
							$path = preg_replace("/\?.*/", "", $foo["path"]);
							
							if (!isset($lookup[$foo["session_id"]])) {
								$lookup[$foo["session_id"]]["path"] = $path;
							}
						}
					}
					
					
					if (!empty($lookup)) {
						$sort = array();
						
						foreach ($lookup as $page) {
							if (!isset($out[$page["path"]])) {
								$out[$page["path"]]["number"] = 0;
								$sort[$page["path"]] = 0;
							}
							$out[$page["path"]]["number"] += 1;
							$sort[$page["path"]] += 1;
						}
						
						// sort
						arsort($sort);
						$dataSort = array();
						foreach ($sort as $path => $foo) {
							$dataSort[$path] = $out[$path];
						}
						
						$out = $dataSort;
					}
				}
				else if ($type == "referer") {
					$lookup = array();
					
					foreach ($this->data as $foo) {
						$foo = (array) json_decode($foo);
						
						// check exclude data
						if ($this->isExcludeData($foo)) {
							continue;
						}
						
						if (isset($foo["http_referer"]) && strpos($foo["http_referer"], "theis-vine.dk") === false) {
							$path = preg_replace("/\?.*/", "", $foo["http_referer"]);
							
							if (empty($path)) {
								$path = "(Direct)";
							}
							
							if (!isset($lookup[$foo["session_id"]])) {
								$lookup[$foo["session_id"]]["path"] = $path;
							}
						}
					}
					
					
					if (!empty($lookup)) {
						$sort = array();
						
						foreach ($lookup as $page) {
							if (!isset($out[$page["path"]])) {
								$out[$page["path"]]["number"] = 0;
								$sort[$page["path"]] = 0;
							}
							$out[$page["path"]]["number"] += 1;
							$sort[$page["path"]] += 1;
						}
						
						// sort
						arsort($sort);
						$dataSort = array();
						foreach ($sort as $path => $foo) {
							$dataSort[$path] = $out[$path];
						}
						
						$out = $dataSort;
					}
				}
				else if ($type == "gclid") {
					$lookup = array();
					$sessionTrack = array();
					
					foreach ($this->data as $foo) {
						$foo = (array) json_decode($foo);
						
						// check exclude data
						if ($this->isExcludeData($foo)) {
							continue;
						}
						
						$search = strtolower("gclid");						
						$path = strtolower($foo["path"]);
						$parts = parse_url($foo["path"]);
						
						// check if path contain search param
						if (!empty($search) && strpos($path, $search) !== false && isset($parts["query"])) {							
							
							// build data
							$path = preg_replace("/\?.*/", "", $foo["path"]);							
							$sessionTrack[$foo["session_id"]] = $path;
							
							if (!isset($lookup[$path])) {
								$lookup[$path]["visit"] = 0;
								$lookup[$path]["order"] = 0;
								$lookup[$path]["visit_price"] = array();
								$lookup[$path]["order_price"] = array();
							}
							
							$lookup[$path]["visit"] += 1;
						}
						
						if (isset($foo["path"])) {
							if (isset($sessionTrack[$foo["session_id"]])) {
								$lookup[$sessionTrack[$foo["session_id"]]]["visit_price"][$foo["session_id"]] = $foo["prices"]->total->productsSubtotal;
							}
							
							if ($foo["path"] == "/betalingsflow/kvittering/" && isset($sessionTrack[$foo["session_id"]])) {								
								$lookup[$sessionTrack[$foo["session_id"]]]["order"] += 1;
								$lookup[$sessionTrack[$foo["session_id"]]]["order_price"][$foo["session_id"]] = $foo["prices"]->total->productsSubtotal;
							}
						}
					}
					
					
					if (!empty($lookup)) {
						$sort = array();
						$sort2 = array();
						foreach ($lookup as $key => $foo) {
							$sort[$key] = $foo["order"];
							$sort2[$key] = $foo["visit"];
							
							$out[$key]["visit"] = $foo["visit"];
							$out[$key]["order"] = $foo["order"];
							$out[$key]["visit_price"] = 0;
							$out[$key]["order_price"] = 0;
							
							if (!empty($foo["visit_price"])) {
								foreach ($foo["visit_price"] as $boo) {
									$out[$key]["visit_price"] += $boo;
								}
							}
							
							if (!empty($foo["order_price"])) {
								foreach ($foo["order_price"] as $boo) {
									$out[$key]["order_price"] += $boo;
								}
							}
						}
						
						// sort
						array_multisort($sort, SORT_DESC, $sort2, SORT_DESC, $out);
					}
				}
			}
			
			return $out;
		}	

		function getBrowser ($u_agent) {
			$bname = 'Unknown';
			$platform = 'Unknown';
			$version = "";
			$b_icon = "fa-globe";
		
			//First get the platform?
			if (preg_match('/linux/i', $u_agent)) {
				$platform = 'linux';
			}
			elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
				$platform = 'mac';
			}
			elseif (preg_match('/windows|win32/i', $u_agent)) {
				$platform = 'windows';
			}
		   
			// Next get the name of the useragent yes seperately and for good reason
			if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
				$bname = 'Internet Explorer';
				$ub = "MSIE";
				$b_icon = "fa-internet-explorer";
			}
			else if(preg_match('/Firefox/i',$u_agent)) {
				$bname = 'Mozilla Firefox';
				$ub = "Firefox";
				$b_icon = "fa-firefox";
			}
			else if(preg_match('/Chrome/i',$u_agent)) {
				$bname = 'Google Chrome';
				$ub = "Chrome";
				$b_icon = "fa-chrome";
			}
			else if(preg_match('/Safari/i',$u_agent)) {
				$bname = 'Apple Safari';
				$ub = "Safari";
				$b_icon = "fa-safari";
			}
			else if(preg_match('/Opera/i',$u_agent)) {
				$bname = 'Opera';
				$ub = "Opera";
				$b_icon = "fa-opera";
			}
			else if(preg_match('/Netscape/i',$u_agent)) {
				$bname = 'Netscape';
				$ub = "Netscape";
			}
		   
			// finally get the correct version number
			$known = array('Version', $ub, 'other');
			$pattern = '#(?<browser>' . join('|', $known) .
			')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
			if (!preg_match_all($pattern, $u_agent, $matches)) {
				// we have no matching number just continue
			}
		   
			// see how many we have
			$i = count($matches['browser']);
			if ($i != 1) {
				//we will have two since we are not using 'other' argument yet
				//see if version is before or after the name
				if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
					$version= $matches['version'][0];
				}
				else {
					$version= $matches['version'][1];
				}
			}
			else {
				$version= $matches['version'][0];
			}
		   
			return array(
				'userAgent' => $u_agent,
				'name'      => $bname,
				'version'   => $version,
				'icon'		=> $b_icon,
				'platform'  => $platform,
				'pattern'    => $pattern
			);
		} 
		
		function getDebug($params) {
			$out = array();
			$params = $this->setArray($params);
			$this->setData($params);
			
			if (!empty($this->data)) {				
				foreach ($this->data as $foo) {
					$foo = (array) json_decode($foo);
					
					if (isset($foo["path"])) {
						$out[] = $foo["path"];
					}
				}
			}
			
			return $out;
		}
	}
?>