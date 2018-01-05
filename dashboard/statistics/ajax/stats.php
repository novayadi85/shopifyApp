<?
	session_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."files/design/php/shopify/dashboard/statistics/class/class.statistic.php");
	
	function priceFormat ($price , $equal = true) {
		//$price = number_format ($price,0,",",".");	
		if($equal) $price = $price / 100;
		$price = number_format($price,2,',','.');
		return $price;
	}
	
	function numberFormat ($number) {
		$number = number_format ($number,0,",",".");	
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
			$pathPriority = array("/cart","/thank_you");
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
					if (isset($flow["cart"]) && $flow["path"] == "/cart") {
						if(!empty($flow["basket"]))
						$sessionCarts[$flow["session_id"]] = $flow["basket"];
					}
				}
			}
			
			// Visitor Products
			$out .= "<div class=\"visitor-products\">";
				if (!empty($sessionCarts)) {
					$products = array();
					
					foreach ($sessionCarts as $carts) {
						if(sizeof($carts)){
							foreach ($carts as $cart) {
								$products[$cart["id"]]["name"] = $cart["title"];
								
								if (!isset($products[$cart["id"]]["quantity"])) {
									$products[$cart["id"]]["amount"] = 0;
									$products[$cart["id"]]["subtotalprice"] = 0;
								}
								
								$products[$cart["id"]]["amount"] += $cart["quantity"];
								$products[$cart["id"]]["subtotalprice"] += $cart["line_price"];
							}
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
							$memberid = $flow["memberid"];
							$lastOrder = "";
							if (!empty($flow["lastOrderDate"])) {
								$lastOrder = date("Y/m/d", strtotime($flow["lastOrderDate"]));
							}
							
							if ($flow["browser"]) {
								$browserIcon = $flow["browser_icon"];
								$browser = $flow["browser"];
							}
						}
					}
					
					$totalProducts = 0;
					$totalPrice = 0;
					
					if (isset($sessionCarts[$session])) {
						foreach ($sessionCarts[$session] as $cart) {
							$totalProducts += $cart["quantity"];
							$totalPrice += $cart["line_price"];
						}
					}
					
					$out .= "<div class=\"visitor-session ".$class."\"><div class=\"row\">";
						$out .= "<div class=\"col-md-5\" style=\"width:15%; padding-right:0;\">";
							$out .= "<a href=\"javascript:;\" class=\"open-visitor-flow\" data-path=\"".$session."\">";
								$out .= "<i class=\"fa fa-plus-square-o\"></i>";	
							$out .= "</a> <i class=\"fa ".$browserIcon."\" title=\"".$browser."\"></i>&nbsp;Visitor ".($num + 1);
						$out .= "</div>";
						$out .= "<div class=\"col-md-2\" style=\"width:20%;padding-right:0;\">";
							//$out .= "<span title=\"Kurv\"><i class=\"fa fa-shopping-bag\"></i> ".numberFormat($totalProducts)." varer (".priceFormat($totalPrice)." kr)</span>";
							$out .= "<a href=\"javascript:;\" class=\"open-session-kurv-detail\" data-visitor=\"Visitor ".($num + 1)."\" data-session=\"".$session."\" data-product=\"".$totalProducts."\" title=\"Kurv\"><i class=\"fa fa-shopping-bag\"></i>&nbsp; ".numberFormat($totalProducts)." varer (".priceFormat($totalPrice)." kr)</a>";
						
						$out .= "</div>";
						$out .= "<div class=\"col-md-2\" style=\"width:7%;padding-right:0;\">";
							if ($ordersCount) {
								$out .= "<a href=\"javascript:;\" data-memberid=\"".$memberid."\" data-session=\"".$session."\"  data-visitor=\"Visitor ".($num + 1)."\"   data-order=\"".numberFormat($ordersCount)."\"  class=\"open-session-order-detail\"><span title=\"Order count\"><i class=\"fa fa-signal\"></i> ".numberFormat($ordersCount)."</span></a>";
							}
						$out .= "</div>";
						$out .= "<div class=\"col-md-2\" style=\"padding-right:0;\">";
							if ($totalSpent) {
								$out .= "<span title=\"Total spent\"><i class=\"fa fa-money\"></i> ".priceFormat($totalSpent,false)." kr</span>";
							}
						$out .= "</div>";
						$out .= "<div class=\"col-md-2\" style=\"padding-right:0;\">";
							if (!empty($lastOrder)) {
								$out .= "<span title=\"Last order date\"><i class=\"fa fa-calendar-check-o\"></i> ".$lastOrder."</span>";
							}
						$out .= "</div>";
						$out .= "<div class=\"col-md-2\" style=\"width:13%;padding-right:0;\">";
							if (file_exists($_SERVER["DOCUMENT_ROOT"]."/files/design/php/shopify/dashboard/statistics/discounts/".$session.".txt")) {
								$out .= "<a href=\"javascript:;\" class=\"btn btn-xs btn-success sent-discount\" data-session=\"".$session."\">Discount Sent!</a>";
								//$out .= "<a href=\"javascript:;\" class=\"btn btn-xs btn-warning give-discount\" data-session=\"".$session."\">Create Discount</a>";	
							}
							else {
								$out .= "<a href=\"javascript:;\" class=\"btn btn-xs btn-warning give-discount\" data-session=\"".$session."\">Give Discount</a>";	
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
	
	else if ($_POST["mode"] == "getVisitorOrderList") {
		$stats = new Statistic();
		$session = $_POST["session"];
		$orders = $stats->get_order($session,false);
		if (!empty($orders)) {
			$html .= "<table cellpadding=\"0\" cellspacing=\"0\">";
				$html .= "<tr><th width=\"25px\"></th><th class=\"text-right\">Date</th><th class=\"text-left\">Gateway</th><th class=\"text-right\">Subtotal</th><th class=\"text-right\">Tax</th><th class=\"text-right\">Total</th><th class=\"text-right\">Status</th></tr>";
				
				$no = 1;
				$amount = 0;
				$total = 0;
				foreach ($orders["orders"] as $order) {
					$html .= "<tr>";
						$html .= "<td>".$order["name"]."</td>";
						$html .= "<td>";
							$html .= date("M d Y",strtotime($order["created_at"]));
						$html .= "</td>";
						$html .= "<td>";
							$html .= $order["gateway"]; 
						$html .= "</td>";
						$html .= "<td class=\"text-right\">";
							$html .= priceFormat($order["subtotal_price"],false);
						$html .= "</td>";
						$html .= "<td class=\"text-right\">";
							$html .= priceFormat($order["total_tax"],false);
						$html .= "</td>";
						$html .= "<td class=\"text-right\">";
							$html .= priceFormat($order["total_price"],false). " kr";
						$html .= "</td>";
						$html .= "<td class=\"text-right\">";
							$html .= $order["financial_status"];
						$html .= "</td>";
					$html .= "</tr>";
					$no++;
					$total_spent = $order["customer"]["total_spent"];
					$amount = $order["customer"]["orders_count"];
				}
				
				$html .= "<tr><td colspan=\"2\">Total</td><td class=\"text-right\"></td><td colspan=\"4\" class=\"text-right\">".priceFormat($total_spent,false)." kr</td></tr>";
				
			$html .= "</table>";
		}
		
		print $html;
		exit();
	}
	
	else if ($_POST["mode"] == "getVisitorCartDetail") {
		
		$stats = new Statistic();
		$session = $_POST["session"];
		$html = "";
		
		$flows = $stats->getDataFlow($session);
		
		
		if (!empty($flows)) {
			$carts = array();
			
			foreach ($flows as $flow) {
				if ($flow["path"] == "/cart") {
					$carts = $flow["basket"];
				}
			}
			
			if (!empty($carts)) {
				$html .= "<table cellpadding=\"0\" cellspacing=\"0\">";
					$html .= "<tr><th width=\"25px\"></th><th>Varer</th><th class=\"text-right\">Antal</th><th class=\"text-right\">Subtotal</th></tr>";
					
					$no = 1;
					$amount = 0;
					$total = 0;
					foreach ($carts as $product) {
						$html .= "<tr>";
							$html .= "<td>".$no."</td>";
							$html .= "<td>";
								$html .= $product["title"];
							$html .= "</td>";
							$html .= "<td class=\"text-right\">";
								$html .= numberFormat($product["quantity"]);
							$html .= "</td>";
							$html .= "<td class=\"text-right\">";
								$html .= priceFormat($product["line_price"]). " kr";
							$html .= "</td>";
						$html .= "</tr>";
						
						$amount += $product["quantity"];
						$total += $product["line_price"];
						$no++;
					}
					
					$html .= "<tr><td colspan=\"2\">Total</td><td class=\"text-right\">".numberFormat($amount)."</td><td class=\"text-right\">".priceFormat($total)." kr</td></tr>";
					
				$html .= "</table>";
			}
			
			print $html;
			exit();
		}
	}
	
	
	
?>