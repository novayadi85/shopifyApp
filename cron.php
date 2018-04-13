<?php
$url = "https://api.rajaongkir.com/starter/city";
$mch = curl_init();
$headers = array(
	'Content-Type: application/json',
	'key: 99988faba4bb93667a04a5592a1f80b9'
);
curl_setopt($mch, CURLOPT_URL, $url );
curl_setopt($mch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($mch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($mch, CURLOPT_CUSTOMREQUEST, "GET"); 
curl_setopt($mch, CURLOPT_TIMEOUT, 10);
curl_setopt($mch, CURLOPT_SSL_VERIFYPEER, false); 
$response = curl_exec($mch);
$response = json_decode($response,true);
curl_close();

$destination = array();
$_GET["postal"] = "80361";
foreach($response["rajaongkir"]["results"] as $city){
	if($city["postal_code"] == $_GET["postal"]){
		$destination = $city;
	}
}

$data = array(
	"query" => array(
		"origin" => "501",
		"destination" =>  $destination["city_id"],
		"weight" =>  2,
		"courier" => "jne"
	)
);


if(sizeof($destination)){
	$url = "https://api.rajaongkir.com/starter/cost";
	$mch = curl_init();
	curl_setopt($mch, CURLOPT_URL, $url );
	curl_setopt($mch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($mch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($mch, CURLOPT_CUSTOMREQUEST, "POST"); 
	curl_setopt($mch, CURLOPT_TIMEOUT, 10);
	curl_setopt($mch, CURLOPT_SSL_VERIFYPEER, false); 
	curl_setopt($mch, CURLOPT_POST, true);
	curl_setopt($mch, CURLOPT_POSTFIELDS, json_encode($data) );
	$response = curl_exec($mch);
	$result = json_decode($response,true);
	curl_close();
	
	print "<pre>";
	print_r($result);
	print "</pre>";
}


