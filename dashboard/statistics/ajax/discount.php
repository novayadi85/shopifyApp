<?
session_start();
require_once($_SERVER["DOCUMENT_ROOT"]."files/design/php/shopify/dashboard/statistics/class/class.statistic.php");
$api = "https://205fb2f0675455a0f71bf0a45dd8da0f:265bc180183890d2e86645fe062e1c16@indosoft-shopify.myshopify.com";
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

function getCountryList(){
	global $api;
	$ch = file_get_contents($api."/admin/countries.json");
	$countries = json_decode($ch , true);
	$options = array();
	if(isset($countries["countries"])){
		foreach($countries["countries"] as $country){
			$options[$country["id"]]["id"] = $country["id"];
			$options[$country["id"]]["text"] = $country["name"];
		}
	}
	
	$options = array_values($options);
	return $options;
}

function createRule($params , $session){
	
	global $api;
	$request = $params;
	$code = $params["discount_code"];
	
	$params = array(
		"title" => $params["headline"],
		"target_type"=> $params["target_type"],
		"target_selection"=> $params["target_selection"],
		"allocation_method"=> $params["allocation_method"],
		"value_type"=> $params["value_type"],
		"value"=> (-1 * $params["value"]),
		"customer_selection"=> "all",
		"entitled_product_ids" =>  $params["entitled_product_ids"],
		"prerequisite_subtotal_range" => $params["prerequisite_subtotal_range"],
		"starts_at" => date("Y-m-d")
	);
	
	
	$params = array_filter($params);
	//print_r($params);
	
	$datajson = json_encode(array('price_rule' => $params));
	$ch = curl_init($api . "/admin/price_rules.json");
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS,$datajson );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($datajson))
	);
	try{
		$server_output = curl_exec ($ch);
	} catch (Exception $e) {
		print json_encode(array("success" => false));
	}
	//print_r($server_output);
	curl_close ($ch);
	$server_output = json_decode($server_output,true);
	$rule = $server_output["price_rule"];
	$params = array(
		array(
			"code" => $code
		)
	);

	if(is_numeric($rule["id"])){
		$datajson = json_encode(array('discount_codes' => $params));
		$ch = curl_init("https://205fb2f0675455a0f71bf0a45dd8da0f:265bc180183890d2e86645fe062e1c16@indosoft-shopify.myshopify.com/admin/price_rules/".$rule["id"]."/batch.json");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$datajson );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($datajson))
		);
		
		$server_output = curl_exec ($ch);
		$request["response"] = $server_output;
		curl_close ($ch);
		$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/files/design/php/shopify/dashboard/statistics/discounts/".$session.".txt","wb");
		fwrite($fp,json_encode($request));
		fclose($fp);
		
		print json_encode(array("success" => $server_output));
	}
	else{
		print json_encode(array("success" => false));
		
	}
	
}

if ($_POST["mode"] == "getFormDiscount") {
	$stats = new Statistic();
	$session = $_POST["session"];
	$html = "";
	$out = array();$tmp = array();
	$flows = $stats->getDataFlow($session);
	if (!empty($flows)) {
		$carts = array();
		
		foreach ($flows as $flow) {
			if ($flow["path"] == "/cart") {
				$carts = $flow["basket"];
			}
		}

		if (!empty($carts)) {
			foreach ($carts as $k => $product) {
				/* 
				if(is_numeric($product["variant_id"])){
					$products[$k]["id"] = $product["variant_id"];
					$products[$k]["text"] = $product["title"];
				}
				else{
					$products[$k]["id"] = $product["product_id"];
					$products[$k]["text"] = $product["title"];
				} 
				*/
				$out[$product["product_id"]]["id"] = $product["product_id"];
				$out[$product["product_id"]]["text"] = $product["product_title"];
			}
			
			$out["products"] = array_values($out);
			$out["country"] = getCountryList();
			
		}
	}
	
	print json_encode($out);
	
}
else if ($_POST["mode"] == "getDiscountsList") {
	$session = $_POST["session"];
	//$stats = new Statistic();
	//$html = $stats->getDiscounts();
	//print $html;
	$html ='
		<div class="btn-group">
			<a class="btn btn-success show-visitor-form" data-form="2" data-session="'.$session.'">Buy for minimum subtotal of order and get % off</a>
			<a class="btn btn-success show-visitor-form" data-form="3" data-session="'.$session.'">Buy for minimum subtotal of order and get $ off</a>
			<a class="btn btn-success show-visitor-form" data-form="4" data-session="'.$session.'">Buy for minimum subtotal of order and get Free Shipping</a>
			<a class="btn btn-success show-visitor-form" data-form="6" data-session="'.$session.'">You get % off</a>
			<a class="btn btn-success show-visitor-form" data-form="7" data-session="'.$session.'">Start chat</a>
		</div>';
		print $html;
	exit();
}

else if ($_POST["mode"] == "createDiscount") {
	$params = array();
	parse_str($_POST["dataForm"] , $params);
	$params["allocation_method"] = "across";
	if(!empty($params["entitled_product_ids"])){
		$entitled_product_ids = explode(",",$params["entitled_product_ids"]);
		$params["entitled_product_ids"] = $entitled_product_ids;
	}
	if(!empty($params["prerequisite_subtotal_range"])){
		$params["prerequisite_subtotal_range"] = array(
			"greater_than_or_equal_to" => $params["prerequisite_subtotal_range"]
		);
	}
	
	if(empty($params["target_selection"])){
		$params["target_selection"] = "entitled";
	}
	
	if(!empty($params["entitled_country_ids"])){
		$entitled_country_ids = explode(",",$params["entitled_country_ids"]);
		$params["entitled_country_ids"] = $entitled_country_ids;
		$params["value"] = 100;
		$params["allocation_method"] = "each";
		
	}
	
	if($params["target_type"] == "shipping_line" && empty($params["entitled_country_ids"])){
		$params["allocation_method"] = "all";
	}

	createRule($params,$_POST["session"]);
}
else if ($_REQUEST["method"] == "deleteDiscount") {
	global $api;
	$session = $_REQUEST["sessionId"];
	if(file_exists($_SERVER["DOCUMENT_ROOT"]."/files/design/php/shopify/dashboard/statistics/discounts/".$session.".txt")){
		$content = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/files/design/php/shopify/dashboard/statistics/discounts/".$session.".txt");
		$content = json_decode($content,true);
		if(isset($content["response"])){
			$response = json_decode($content["response"],true);
			$ch = curl_init($api . "/admin/price_rules/".$response["discount_code_creation"]["price_rule_id"].".json");
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			try{
				$server_output = curl_exec ($ch);
				unlink($_SERVER["DOCUMENT_ROOT"]."/files/design/php/shopify/dashboard/statistics/discounts/".$session.".txt");
				print json_encode(array("success" => true));
			} catch (Exception $e) {
				print json_encode(array("success" => false));
			}
			
			curl_close ($ch);
		}
		else{
			print_r($content);
		}
	}
	else{
		print json_encode(array("success" => "file not found"));
	}
	
}
