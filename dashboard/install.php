<?php
	session_start();
	require_once($_SERVER["DOCUMENT_ROOT"].'/files/design/php/shopify/app/classes/shopify.php');
	require_once($_SERVER["DOCUMENT_ROOT"].'/files/design/php/shopify/dashboard/config.php');
	
	// Get store data from database if sotre already install the app
	// Set $_SESSION['token']
	
	if (empty($_SESSION['token'])) {
		// Authorize the app
		$shop = isset($_POST['shop']) ? $_POST['shop'] : $_GET['shop'];
		if (!empty($shop)) {
			$shopifyClient = new ShopifyClient($shop, "", SHOPIFY_API_KEY, SHOPIFY_SECRET);
		
			// Redirect to authorize url
			header("Location: " . $shopifyClient->getAuthorizeUrl(SHOPIFY_SCOPE, SHOPIFY_REDIRECT_URL));
			exit;
		}
		else {
?>
        <p>Install this app in a shop to get access to its private admin data.</p> 
        
        <form action="" method="post">
            <label for='shop'><strong>The URL of the Shop</strong> 
                <span class="hint">(enter it exactly like this: myshop.myshopify.com)</span> 
            </label> 
            <p> 
                <input id="shop" name="shop" size="45" type="text" value="" /> 
                <input name="commit" type="submit" value="Install" /> 
            </p> 
        </form>
<?php
		}
	}
	else {
		// Redirect to admin page
		header("Location: ". SHOPIFY_PAGE);
		exit;
	}
?>