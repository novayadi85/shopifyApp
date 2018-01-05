<?php
	session_start();
	require_once($_SERVER["DOCUMENT_ROOT"].'/files/design/php/shopify/app/classes/shopify.php');
	require_once($_SERVER["DOCUMENT_ROOT"].'/files/design/php/shopify/dashboard/config.php');
	
	if (isset($_GET['code'])) {
        $shopifyClient = new ShopifyClient($_GET['shop'], "", SHOPIFY_API_KEY, SHOPIFY_SECRET);
        session_unset();
		
		if ($shopifyClient->validateSignature($_GET)) {
			// Generate Access Token And Session
			$token = $shopifyClient->getAccessToken($_GET['code']);
			
			if ($token != '') {
				$_SESSION['shop'] = $_GET['shop'];

				// Redirect to admin page
				header("Location: ". "https://".$_SESSION['shop']."/admin/apps/".SHOPIFY_API_KEY);
				//header("Location: ". "index.php");
				exit;
			}
		}
		else {
			die ("Invalid Signature! Signature not valid");
		}
    }
	else {
		die ("Invalid Request! Request or redirect did not come from Shopify");
	}
?>