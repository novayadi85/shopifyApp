<?php 
if($_REQUEST["method"] == "stats"){
	$logFile = "logs-".date("y-m");
	$filepath = $_SERVER["DOCUMENT_ROOT"].'/files/design/php/shopify/dashboard/logs/'. $logFile .".log";
	$total =  array();
	if(!empty($_REQUEST["cart"])){
		$cart = $_REQUEST["cart"];
		$total = array(
			"total" => array(
				"productsSubtotal" => $cart["total_price"]
			)
		);
		
		if(empty($_REQUEST["sessionId"])){
			$_REQUEST["sessionId"] = $cart["token"];
		}
		
	}
	
	

	$array = array(
		"http_user_agent" => $_REQUEST['USER_AGENT'],
		"ip" => $_REQUEST["IP"] ,
		"datetime" => date('Y-m-d H:i:s', $_REQUEST["timestamp"]),
		"http_referer" => $_REQUEST["referrer"],
		"session_id" => $_REQUEST["sessionId"],
		"memberid" =>  (!empty($_REQUEST["memberid"])) ? $_REQUEST["memberid"] : null ,
		"cart" => json_encode($_REQUEST["cart"]),
		"prices" => json_encode($total)
	); 

	if(!empty($_REQUEST["params"])){
		foreach($_REQUEST["params"] as $k => $param){
			if($k == "pageurl"){
				if(isset($_REQUEST["shop"])){
					$array["path"] = trim(str_replace($_REQUEST["shop"],"",$param));
				}
				else{
					$array["path"] = trim(str_replace($_SERVER["HTTP_HOST"],"",$param));
				}
				
			}
		}
	}

	if (!file_exists($filepath)) {
		$fp = fopen($filepath,"w");
		fclose($fp);
		chmod($filepath, 0777);
	}
	if (file_exists($filepath) && !empty($array)) {
		if ($fp = fopen($filepath,"a")) {
			file_put_contents($filepath,json_encode($array). "\n", FILE_APPEND);
			fclose($fp);
		}
	}

	echo json_encode($array);
}
else if($_REQUEST["method"] == "getOffer"){
	$html = "";
	$out =  array();
	$cart = $_REQUEST["cart"];
	if(empty($_REQUEST["sessionId"])){
		$_REQUEST["sessionId"] = $cart["token"];
	}
	$sessionId = $_REQUEST["sessionId"];
	$out['sessionId'] = $sessionId;
	
	if (file_exists($_SERVER["DOCUMENT_ROOT"]."/files/design/php/shopify/dashboard/statistics/discounts/".$sessionId.".txt")){
		$content = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/files/design/php/shopify/dashboard/statistics/discounts/".$sessionId.".txt");
		$content = json_decode($content,true);
		$out["error"] = false;
		$html .="<div class=\"text-center\">";
		$html .= "<p>".$content["subheadline"]."</p>";
		$html .= "<div class=\"voucer-code\"> <span>VOUCHER : <strong>#".$content["discount_code"]."</strong></span></div>";
		$html .= "<p>".$content["message"]."</p>";
		$html .="</div>";
		$out["voucher"] = $content["discount_code"];
		$out["html"] = $html;
		$out["title"] = $content["headline"];
	}
	else{
		$out["error"] = true;
	}
	echo json_encode($out);
}
?>