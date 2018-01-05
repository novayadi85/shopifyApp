<?php
	// Define APP Settings
	define('SHOPIFY_APP_NAME','Siteloom Dashboard');
    define('SHOPIFY_API_KEY','406bddfc82e266771c3a62f073349e71');
    define('SHOPIFY_SECRET','e56d08cdcd89a14250d441ac189b6c18');
    define('SHOPIFY_SCOPE','read_content,read_analytics,read_script_tags, write_script_tags, write_script_tags,write_content,write_themes,read_themes,write_themes,read_products,write_products,read_customers,write_customers,read_orders,write_orders,read_script_tags,write_script_tags,read_fulfillments,write_fulfillments,read_shipping,write_shipping');
	define('SHOPIFY_REDIRECT_URL','https://www.theis-vine.dk/files/design/php/shopify/dashboard/start.php'); // Should be the same with redirect in app admin
	
	define('SHOPIFY_ROOT',$_SERVER["DOCUMENT_ROOT"].'/files/design/php/shopify/dashboard/');
	define('SHOPIFY_PAGE',"page.php");
?>