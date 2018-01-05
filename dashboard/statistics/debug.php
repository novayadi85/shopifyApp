<?
	require_once($_SERVER["DOCUMENT_ROOT"]."/config.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/cms/db.inc.php");
	global $_SITELOOM;
	global $connection;
	require_once($_SERVER["DOCUMENT_ROOT"]."backend/system/statistics/class/class.statistic.php");
	
	$stats = new Statistic();
	$stats->debug = true;
	if ($_SERVER["REMOTE_ADDR"] == "125.162.138.234") {
		//$data = $stats->getDataWeek(2017,52,"gclid");
		$data = $stats->getDataLive();
		$data = $stats->getDataFlow(array("3b4715b28fc448132034b1ac2752cea7"));
		
		/*echo "<pre>";
		print_r($data);
		echo "</pre>";*/
	}
	else {
		$data = $stats->getDataWeek(2017,52,"checkout-flow", 4);
	}
	
	
	/*echo "<pre>";
	print_r($data);
	echo "</pre>";
	echo "<br />";
	echo "<br />";*/
?>