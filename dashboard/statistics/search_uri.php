<?
	require_once($_SERVER["DOCUMENT_ROOT"]."backend/system/statistics/class/class.statistic.php");
	
	$stats = new Statistic("/home/siteloom/htdocs/theis/siteloomlog/2017-50_log.txt");
	
	// set sort type
	$sortNumber = false;
	if (isset($_REQUEST["sort_number"]) && $_REQUEST["sort_number"] == "true") {
		$sortNumber = true;
	}
	
	$stats->config["sort_by_number"] = $sortNumber;
	
	// set search type	
	if (isset($_REQUEST["search"])) {
		$search = $_REQUEST["search"];
		
		if (!empty($search)) {
			$search = explode(" ",$search);
		}
		
		$data = $stats->getDataByURI($search);
	}
	
	echo "<pre>";
	print_r($data);
	echo "</pre>";
	
	print "All search terms: ".sizeof($data);
	print "<br>";
	print "Total search terms: ".array_sum($data);
?>