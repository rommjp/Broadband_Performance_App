<?php
/************************
Author: Rommel Poggenberg
Subject: FIT5147
Server side script receiving two filter values 'month' and 'metric'
Used to query a mongo database to retrieve the data in form of documents.
Data is encoded to JSON and sent client side to display in the web app in highcharts graph.
************************/

$month = isset($_GET['month'])? $_GET['month']: "";
$metric = isset($_GET['metric'])? $_GET['metric']: "";

$state_mapping=array('Feb2020'=>array('Feb2020'),
'Nov2019'=>array('Nov2019'),
'Aug2019'=>array('Aug2019'),
'May2019'=>array('May2019'),
'Feb2019'=>array('Feb2019'),
'Nov2018'=>array('Nov2018'),
'July2018'=>array('July2018'),
'March2018'=>array('March2018'),
'all'=>array('all'));


$chart_data=array();

foreach ($state_mapping[$month] as $current_month){
	
	
	$filter = ['month' => $month, 'chart_type' => $metric];
	$options = [
		'projection' => ['_id' => 0, 'results' => 1],
		'sort' => ['x' => -1],
	];

	$manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
	$query = new MongoDB\Driver\Query($filter, $options);
	$cursor = $manager->executeQuery('performance_data.charts', $query);

	$map_data=array();
	$array = array();

	foreach ($cursor as $map_info) {
		$array = json_decode(json_encode($map_info), True);
	}

	
	if (count($array) > 0){
		foreach($array['results'] as $key=>$value){
			$data_array = array();
			foreach($value as $key1=>$value1){
				#print_r($value1);
				array_push($data_array, $value1);
			}
			
			$map_data[$key]=$data_array;
		}
	}
	
	$chart_data[$current_month]=$map_data;
}

echo json_encode(array('chart'=> $chart_data,'chart_type'=>$metric));

?>