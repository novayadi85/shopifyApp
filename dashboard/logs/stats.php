<?
	require_once($_SERVER["DOCUMENT_ROOT"]."/config.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/cms/db.inc.php");
	global $_SITELOOM;
	global $connection;
	require_once($_SERVER["DOCUMENT_ROOT"]."backend/system/statistics/class/class.statistic.php");
	
	function priceFormat ($price) {
		//$price = number_format ($price,2,",",".");	
		return $price;
	}
	
	function numberFormat ($number) {
		//$number = number_format ($number,0,",",".");	
		return $number;
	}
	
	function listHtml ($stats, $data, $openPath, $priorityPath = false) {
		$out = "";
		if (!empty($data)) {
			$i = 1;
			foreach ($data as $key => $foo) {
				$visitor = "0";
				$class = "";
				$sessionIds = "";
				
				if ($i%2 == 0) {
					$class .= "visitor-odd ";
				}
				
				if ($foo["visitor"]) {
					$visitor = $foo["visitor"];
					
					if ($priorityPath) {
						$class .= "set-bold";
					}
					
					foreach ($foo["session_id"] as $session) {
						$sessionIds .= " ".$session;
					}
					
					$sessionIds = trim($sessionIds);
				}
				
				$price = "0";
				if ($foo["price"]) {
					$price = $foo["price"];
				}
				
				$out .= "<tr class=\"".$class."\">";
					$out .= "<td>".$key."</td>";
					$out .= "<td>".$visitor." <a href=\"javascript:;\" class=\"open-visitor-detail\" data-session-id=\"".$sessionIds."\" data-path=\"".$key."\" data-price=\"".priceFormat($price)."\"><i class=\"fa fa-user\"></i></a></td>";
					$out .= "<td class=\"text-right\">".priceFormat($price)." kr</td>";
				$out .= "</tr>";
				
				$i++;
			}
		}
		
		return $out;
	}
	
	if ($_POST["mode"] == "getLiveVisitor") {
		$openPath = $_POST["openPath"];
		$stats = new Statistic();
		$data = $stats->getDataLive();
		
		// parse html Campaign
		if (isset($data["campaign"]) && !empty($data["campaign"])) {
			$campaign = "";
			foreach ($data["campaign"] as $key => $foo) {
				$campaign .= "<span>".$foo." : (".$key.")</span>";
			}
			
			$data["campaign"] = $campaign;
		}
		else {
			$data["campaign"] = "";	
		}
		
		// parse html Search
		if (isset($data["search"]) && !empty($data["search"])) {
			$search = "";
			foreach ($data["search"] as $key => $foo) {
				if (!empty($foo)) {
					$search .= "<span>".$foo."</span>";	
				}
			}
			
			$data["search"] = $search;
		}
		else {
			$data["search"] = "";	
		}
		
		// parse html Path
		if (isset($data["path"]) && !empty($data["path"])) {
			$pathPriority = array("/kurv/","/betalingsflow/step1/","/betalingsflow/step2/","/betalingsflow/kvittering/");
			$priorities = array();
			
			foreach ($pathPriority as $foo) {
				$priorities[$foo] = array();
				
				if (isset($data["path"][$foo])) {
					$priorities[$foo] = $data["path"][$foo];
					unset($data["path"][$foo]);
				}
			}
			
			$path = "";
			$path .= listHtml($stats,$priorities,$openPath,true);
			
			$path .= "<tr class=\"visitor-sparator\">";
				$path .= "<td>&nbsp;</td>";
				$path .= "<td>&nbsp;</td>";
				$path .= "<td>&nbsp;</td>";
			$path .= "</tr>";
			
			$path .= listHtml($stats,$data["path"],$openPath);
			
			$data["path"] = $path;
		}
		else {
			$data["path"] = "";	
		}
		
		$data["total_price"] = priceFormat($data["total_price"]);
		
		print json_encode($data);
		exit();
	}
	
	else if ($_POST["mode"] == "getVisitorDetail") {
		$sessions = explode(" ", $_POST["session"]);
		$stats = new Statistic();
		
		$out = "";
		
		if (!empty($sessions)) {
			$flows = $stats->getDataFlow($sessions);
			
			$sessionCarts = array();
			if (!empty($flows)) {
				// get last cart pr session id
				foreach ($flows as $flow) {
					if (isset($flow["action"]) && $flow["action"] == "basketupdate") {
						$sessionCarts[$flow["session_id"]] = $flow["basket"];
					}
				}
			}
			
			// Visitor Products
			$out .= "<div class=\"visitor-products\">";
				if (!empty($sessionCarts)) {
					$products = array();
					
					foreach ($sessionCarts as $carts) {
						foreach ($carts as $cart) {
							$products[$cart["productid"]]["name"] = $cart["name"];
							
							if (!isset($products[$cart["productid"]]["amount"])) {
								$products[$cart["productid"]]["amount"] = 0;
								$products[$cart["productid"]]["subtotalprice"] = 0;
							}
							
							$products[$cart["productid"]]["amount"] += $cart["amount"];
							$products[$cart["productid"]]["subtotalprice"] += $cart["subtotalprice"];
						}
					}
					
					$out .= "<table cellpadding=\"0\" cellspacing=\"0\">";
						$out .= "<tr><th></th><th>Varer</th><th class=\"text-right\">Antal</th><th class=\"text-right\">Subtotal</th></tr>";
						
						$no = 1;
						$amount = 0;
						$total = 0;
						foreach ($products as $product) {
							$out .= "<tr>";
								$out .= "<td>".$no."</td>";
								$out .= "<td>";
									$out .= $product["name"];
								$out .= "</td>";
								$out .= "<td class=\"text-right\">";
									$out .= numberFormat($product["amount"]);
								$out .= "</td>";
								$out .= "<td class=\"text-right\">";
									$out .= priceFormat($product["subtotalprice"]). " kr";
								$out .= "</td>";
							$out .= "</tr>";
							
							$amount += $product["amount"];
							$total += $product["subtotalprice"];
							$no++;
						}
						
						$out .= "<tr><td colspan=\"2\">Total</td><td class=\"text-right\">".numberFormat($amount)."</td><td class=\"text-right\">".priceFormat($total)." kr</td></tr>";
						
					$out .= "</table>";
				}
				else {
					$out .= "Du har ingen varer i indkøbskurven";	
				}
			$out .= "</div>";
			
			
			// Visitor List
			$out .= "<div class=\"visitor-detail-list\">";
			
			$i = 1;
			foreach ($sessions as $num => $session) {
				$class = "";
				
				if ($i%2 == 0) {
					$class = "visitor-session-odd";
				}
				
				if (!empty($flows)) {
					$ordersCount = 0;
					$totalSpent = 0;
					
					foreach ($flows as $flow) {
						if ($flow["session_id"] == $session) {
							$ordersCount = $flow["ordersCount"];
							$totalSpent = $flow["totalSpent"];
							
							$lastOrder = "";
							if (!empty($flow["lastOrderDate"])) {
								$lastOrder = date("Y/m/d", strtotime($flow["lastOrderDate"]));
							}
						}
					}
					
					$totalProducts = 0;
					$totalPrice = 0;
					
					if (isset($sessionCarts[$session])) {
						foreach ($sessionCarts[$session] as $cart) {
							$totalProducts += $cart["amount"];
							$totalPrice += $cart["subtotalprice"];
						}
					}
					
					$out .= "<div class=\"visitor-session ".$class."\"><div class=\"row\">";
						$out .= "<div class=\"col-md-5\" style=\"width:15%; padding-right:0;\">";
							$out .= "<a href=\"javascript:;\" class=\"open-visitor-flow\" data-path=\"".$session."\">";
								$out .= "<i class=\"fa fa-plus-square-o\"></i>";	
							$out .= "</a> Visitor ".($num + 1);
						$out .= "</div>";
						$out .= "<div class=\"col-md-2\" style=\"width:20%;padding-right:0;\">";
							$out .= "<span title=\"Kurv\"><i class=\"fa fa-shopping-bag\"></i> ".numberFormat($totalProducts)." varer (".priceFormat($totalPrice)." kr)</span>";
						$out .= "</div>";
						$out .= "<div class=\"col-md-2\" style=\"width:7%;padding-right:0;\">";
							if ($ordersCount) {
								$out .= "<span title=\"Order count\"><i class=\"fa fa-signal\"></i> ".numberFormat($ordersCount)."</span>";
							}
						$out .= "</div>";
						$out .= "<div class=\"col-md-2\" style=\"width:13%;padding-right:0;\">";
							if ($totalSpent) {
								$out .= "<span title=\"Total spent\"><i class=\"fa fa-money\"></i> ".priceFormat($totalSpent)." kr</span>";
							}
						$out .= "</div>";
						$out .= "<div class=\"col-md-2\" style=\"width:11%;padding-right:0;\">";
							if (!empty($lastOrder)) {
								$out .= "<span title=\"Last order date\"><i class=\"fa fa-calendar-check-o\"></i> ".$lastOrder."</span>";
							}
						$out .= "</div>";
						$out .= "<div class=\"col-md-2\" style=\"width:13%;padding-right:0;\">";
							if (file_exists($_SERVER["DOCUMENT_ROOT"]."/backend/system/statistics/discounts/".$session.".txt")) {
								$out .= "<a href=\"javascript:;\" class=\"btn btn-xs green\">Discount Sent!</a>";
							}
							else {
								$out .= "<a href=\"javascript:;\" class=\"btn btn-xs blue give-discount\" data-session=\"".$session."\">Give Discount</a>";	
							}
						$out .= "</div>";
					$out .= "</div></div>";
				
				
				
					$out .= "<div class=\"visitor-flow\" data-path=\"".$session."\">";
					
					foreach ($flows as $flow) {
						if ($flow["session_id"] == $session) {
							if (isset($flow["path"])) {
								$out .= "<div><i class=\"fa fa-link\"></i> ".$flow["path"]."</div>";
							}
							else if (isset($flow["action"]) && $flow["action"] == "basketupdate") {
								$productKey = count($flow["basket"]) - 1;
								$out .= "<div><i class=\"fa fa-shopping-bag\"></i> add to cart:  (".$flow["basket"][$productKey]["amount"].") ".$flow["basket"][$productKey]["name"]." | ".priceFormat($flow["basket"][$productKey]["subtotalprice"])." kr</div>";
							}
						}
					}
					
					$out .= "</div>";
				}
				
				$i++;
			}
			$out .= "</div>";
		}
		
		print $out;
		exit();
	}
	
	else if ($_POST["mode"] == "makeFileSession") {
		$out["error"] = true;
		
		if (!empty($_POST["session"])) {
			$session = $_POST["session"];
			$message = $_POST["message"];
			
			$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/backend/system/statistics/discounts/".$session.".txt","wb");
			fwrite($fp,$message);
			fclose($fp);

			$out["error"] = false;
		}
		
		print json_encode($out);
		exit();
	}
	
	else if ($_POST["mode"] == "getStatsWeek") {
		$year = $_POST["year"];
		$week = $_POST["week"];
		$type = $_POST["type"];
		$day = false;
		$html = "";
		
		if (isset($_POST["day"])) {
			$day = $_POST["day"];
		}
		
		$stats = new Statistic();
		$pages = $stats->getDataWeek($year, $week, $type, $day);
			
		if ($type == "checkout-flow") {
			if ($year == "2017" && $week <= "51") {
				$html .= "<div class=\"Metronic-alerts alert alert-warning fade in\" style=\"padding:7px 10px; margin-bottom:10px; width:70%;\">Not correct data...</div>";
			}
			
			$html .= "<table cellpadding=\"0\" cellspacing=\"0\" width=\"70%\">
						<tr>
							<th></th>
							<th></th>
							<th class=\"text-right\">Lost</th>
							<th></th>
							<th class=\"text-right\">Lost</th>
						</tr>";
			
			if (!empty($pages)) {
				foreach ($pages as $path => $detail) {
					$html .= "<tr>";
						$html .= "<td>".$path."</td>";
						$html .= "<td class=\"text-right\">".numberFormat($detail["number"])." <i class=\"fa fa-user\"></i></td>";
						$html .= "<td class=\"text-right\">";
							if (!empty($detail["lost_number"])) {
								$html .= numberFormat($detail["lost_number"])." <i class=\"fa fa-user\"></i>";
							}
						$html .= "</td>";
						$html .= "<td class=\"text-right\">".priceFormat($detail["price"])." kr</td>";
						$html .= "<td class=\"text-right\">";
						if (!empty($detail["lost_price"])) {
							$html .= priceFormat($detail["lost_price"])." kr";
						}
						$html .= "</td>";
					$html .= "</tr>";
				}
			}
			$html .= "</table>";
		}
		else if ($type == "search") {
			$html .= "<table cellpadding=\"0\" cellspacing=\"0\" width=\"50%\">";
			
			if (!empty($pages)) {
				foreach ($pages as $search => $visit) {
					$html .= "<tr>";
						$html .= "<td>".$search."</td>";
						$html .= "<td class=\"text-right\">".numberFormat($visit)."</td>";
					$html .= "</tr>";
				}
			}
			
			$html .= "</table>";
		}
		else if ($type == "campaigns") {
			$html .= "<table cellpadding=\"0\" cellspacing=\"0\" width=\"50%\">";
			
			if (!empty($pages)) {
				foreach ($pages as $search => $visit) {
					$html .= "<tr>";
						$html .= "<td>".$search."</td>";
						$html .= "<td class=\"text-right\">".numberFormat($visit)."</td>";
					$html .= "</tr>";
				}
			}
			
			$html .= "</table>";
		}
		else if ($type == "last-page") {
			$html .= "<table cellpadding=\"0\" cellspacing=\"0\" width=\"70%\">";
			
			if (!empty($pages)) {
				foreach ($pages as $search => $visit) {
					$html .= "<tr>";
						$html .= "<td>".$search."</td>";
						$html .= "<td class=\"text-right\">".numberFormat($visit["number"])."</td>";
						$html .= "<td class=\"text-right\" style=\"min-width:120px;\">".priceFormat($visit["price"])." kr</td>";
					$html .= "</tr>";
				}
			}
			
			$html .= "</table>";
		}
		else if ($type == "start-page") {
			$html .= "<table cellpadding=\"0\" cellspacing=\"0\" width=\"50%\">";
			
			if (!empty($pages)) {
				foreach ($pages as $search => $visit) {
					$html .= "<tr>";
						$html .= "<td>".$search."</td>";
						$html .= "<td class=\"text-right\">".numberFormat($visit["number"])."</td>";
					$html .= "</tr>";
				}
			}
			
			$html .= "</table>";
		}
		else if ($type == "referer") {
			$html .= "<table cellpadding=\"0\" cellspacing=\"0\" width=\"50%\">";
			
			if (!empty($pages)) {
				foreach ($pages as $search => $visit) {
					$html .= "<tr>";
						$html .= "<td>".$search."</td>";
						$html .= "<td class=\"text-right\">".numberFormat($visit["number"])."</td>";
					$html .= "</tr>";
				}
			}
			
			$html .= "</table>";
		}
		else if ($type == "gclid") {
			$html .= "<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">";
			
			$html .= "<tr>";
				$html .= "<th>".$search."</th>";
				$html .= "<th class=\"text-right\"></th>";
				$html .= "<th class=\"text-right\">Order</th>";
				$html .= "<th class=\"text-right\"></th>";
				$html .= "<th class=\"text-right\">Order</th>";
			$html .= "</tr>";
					
			if (!empty($pages)) {
				foreach ($pages as $search => $visit) {
					$html .= "<tr>";
						$html .= "<td>".$search."</td>";
						$html .= "<td class=\"text-right\">".numberFormat($visit["visit"])." <i class=\"fa fa-user\"></i></td>";
						$html .= "<td class=\"text-right\">".numberFormat($visit["order"])." <i class=\"fa fa-user\"></i></td>";
						$html .= "<td class=\"text-right\">".priceFormat($visit["visit_price"])." kr</td>";
						$html .= "<td class=\"text-right\">".priceFormat($visit["order_price"])." kr</td>";
					$html .= "</tr>";
				}
			}
			
			$html .= "</table>";
		}
		
		print $html;
		exit();
	}
?>