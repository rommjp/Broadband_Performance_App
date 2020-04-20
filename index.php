<?php
/**********************************
Author: Rommel Poggenberg
Subject: FIT5147
Web app showing visualization of Broadband performance data from the ACCC.
Showing various metrics of NBN Broadband plans across 3 speed tiers and across all access technologies (FTTC, FTTP, FTTN, HFC).
Also displaying technical quality measures such as webpage_load, latency and frequency of packet loss at the customer modem. 
Requirements:
- Apache Web Service 
- MongoDB Community Edition 4.2.1
- PHP Version 7.1.33 with mongodb driver installed

************************************/

$metric_dropdown="
		<select id='metric_id'>
			<option value='speeds' select>speeds</option>
			<option value='technology - nbn'>technology - nbn</option>
			<option value='avg_dl_speed'>avg_dl_speed</option>
			<option value='hour_of_day'>hour_of_day</option>
			<option value='distribution_of_speed'>distribution_of_speed</option>
			<option value='webpage_load'>webpage_load</option>
			<option value='latency'>latency</option>
			<option value='packet_loss'>packet_loss</option>
		</select>";
		
$month_dropdown="
		<select id='month_id'>
			<option value='Feb2020'>Feb2020</option>
			<option value='Nov2019'>Nov2019</option>
			<option value='Aug2019'>Aug2019</option>
			<option value='May2019'>May2019</option>
			<option value='Feb2019'>Feb2019</option>
			<option value='Nov2018'>Nov2018</option>
			<option value='July2018'>July2018</option>
			<option value='March2018'>March2018</option>
			<option value='all'>ALL MONTHS</option>
		</select>";		

		
$html_display="
<div class='container'>

      <h1>Internet Broadband Performance</h1><br>
		Metric: {$metric_dropdown}
		Month: {$month_dropdown}
		<button id='filter_id' type='button' enabled>Refresh Display</button>
</div>		
";
?>

<!DOCTYPE html>
<html lang="en">
  <head>


    <!-- Le styles -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://code.highcharts.com/highcharts.js"></script>
	<script src="https://code.highcharts.com/modules/exporting.js"></script>
	<script src="https://code.highcharts.com/modules/export-data.js"></script>
	<script src="https://code.highcharts.com/modules/accessibility.js"></script>	



 <script type="text/javascript">

$(document).ready(function() {
	
	$("#filter_id").click(function() {
		input_data={};
		input_data['month']=$('#month_id').prop('selected', true).val();
		input_data['metric']=$('#metric_id').prop('selected', true).val();
		console.log($(this));

		$.ajax({
			type: 'GET',
			url: 'getDropDowndata2.php',
			data: input_data,
			dataType: 'json',
			async:false,
			cache: false,
			success:function(response) {
					console.log(response['chart']);
					var count=0;
									
					$('#container0').html("");
					$('#container1').html("");
					$('#container2').html("");
					$('#container3').html("");
					$('#container4').html("");
					
					$.each( response['chart'], function( month, values ) {
						
						var chart_index='container'+count.toString()
						
						if (month !='all'){
						
							if (response['chart_type']=='speeds'){
								var chart_title="NBN plan speeds delivered during busy hours and the busiest hour over the month of "+month;
								$('#note_id').html("");
								$('#title_id').html("");
							
								Highcharts.chart(chart_index, {
									chart: {
										type: 'column'
									},
									title: {
										text: chart_title
									},
									xAxis: {
										categories: ['Aussie Broadband', 'Dodo & iPrimus', 'Exetel', 'iiNet', 'MyRepublic', 'Optus', 'Telstra', 'TPG'],
										crosshair: true
									},
									yAxis: {
										min: 0,
										title: {
											text: 'Download Speed (% of Max Plan Speed)'
										}
									},
									tooltip: {
										headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
										pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
											'<td style="padding:0"><b>{point.y:.1f} %</b></td></tr>',
										footerFormat: '</table>',
										shared: true,
										useHTML: true
									},
									plotOptions: {
												column: {
											pointPadding: 0.2,
											borderWidth: 0
										}
									},
									series: [{
										name: 'Download:busy hours',
										data: values['Download:busy hours']
									}, {
										name: 'Download:busiest hour',
										data: values['Download:busiest hour']
									}, {
										name: 'Upload:busy hours',
										data: values['Upload:busy hours']
									}]
								})	

							}else if (response['chart_type']=='outages'){
								$('#note_id').html("");
								$('#title_id').html("");
								
								Highcharts.chart(chart_index, {
									chart: {
										type: 'column'
									},
									title: {
										text: month
									},
									xAxis: {
										categories: ['Aussie Broadband', 'Dodo & iPrimus', 'Exetel', 'iiNet', 'MyRepublic', 'Optus', 'Telstra', 'TPG'],
										crosshair: true
									},
									yAxis: {
										min: 0,
										title: {
											text: 'Percentage (%)'
										}
									},
									tooltip: {
										headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
										pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
											'<td style="padding:0"><b>{point.y:.1f} %</b></td></tr>',
										footerFormat: '</table>',
										shared: true,
										useHTML: true
									},
									plotOptions: {
												column: {
											pointPadding: 0.2,
											borderWidth: 0
										}
									},
									series: [{
										name: 'All services',
										data: values['All services']
									}, {
										name: 'Average daily outages excluding services that are unable to achieve maximum plan speeds',
										data: values['Average daily outages excluding services that are unable to achieve maximum plan speeds']
									}]									
								})	
								
								
							}else if (response['chart_type']=='webpage_load'){
								
								var chart_title = "Web page loading time (seconds) for month of "+month;
								$('#note_id').html("Average time for a website to load in the chrome internet browser");	
								$('#title_id').html("");								
								
								Highcharts.chart(chart_index, {
									chart: {
										type: 'column'
									},
									title: {
										text: chart_title
									},
									xAxis: {
										categories: ['Aussie Broadband', 'Dodo & iPrimus', 'Exetel', 'iiNet', 'MyRepublic', 'Optus', 'Telstra', 'TPG', 'ADSL'],
										crosshair: true
									},
									yAxis: {
										min: 0,
										title: {
											text: 'Web page loading time (sec)'
										}
									},
									tooltip: {
										headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
										pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
											'<td style="padding:0"><b>{point.y:.1f} sec</b></td></tr>',
										footerFormat: '</table>',
										shared: true,
										useHTML: true
									},
									plotOptions: {
												column: {
											pointPadding: 0.2,
											borderWidth: 0
										}
									},
									series: [{
										name: 'webpage_load',
										data: values['webpage_load']
									}]									
								})								
							}else if (response['chart_type']=='latency'){
								var chart_title = "Latency (milliseconds) for month of "+month;
								
								var note_string="High latency can impact online experiences such as video conferencing, media streaming and online gaming. <br>  Latency is the total time it takes a data packet to travel from one network node to another.";
								$('#note_id').html(note_string);
								$('#title_id').html("");								
								Highcharts.chart(chart_index, {
									chart: {
										type: 'column'
									},
									title: {
										text: chart_title
									},
									xAxis: {
										categories: ['Aussie Broadband', 'Dodo & iPrimus', 'Exetel', 'iiNet', 'MyRepublic', 'Optus', 'Telstra', 'TPG', 'ADSL'],
										crosshair: true
									},
									yAxis: {
										min: 0,
										title: {
											text: 'Latency (ms)'
										}
									},
									tooltip: {
										headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
										pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
											'<td style="padding:0"><b>{point.y:.1f} ms</b></td></tr>',
										footerFormat: '</table>',
										shared: true,
										useHTML: true
									},
									plotOptions: {
												column: {
											pointPadding: 0.2,
											borderWidth: 0
										}
									},
									series: [{
										name: 'latency',
										data: values['latency']
									}]									
								})							
							}else if (response['chart_type']=='technology - nbn'){
								
								var chart_title = "NBN and ADSL plan speeds delivered during busy hours by technology for month of "+month;
								$('#note_id').html("");		
								$('#title_id').html("");								
								Highcharts.chart(chart_index, {
									chart: {
										type: 'column'
									},
									title: {
										text: chart_title
									},
									xAxis: {
										categories: ['FTTP', 'FTTN', 'HFC', 'FTTC'],
										crosshair: true
									},
									yAxis: {
										min: 0,
										title: {
											text: 'Percentage of Download Plan (%)'
										}
									},
									tooltip: {
										headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
										pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
											'<td style="padding:0"><b>{point.y:.1f} %</b></td></tr>',
										footerFormat: '</table>',
										shared: true,
										useHTML: true
									},
									plotOptions: {
												column: {
											pointPadding: 0.2,
											borderWidth: 0
										}
									},
									series: [{
										name: 'All services as a percentage of maximum plan speed',
										data: values['All services as a percentage of maximum plan speed']
									},{
										name: 'Potential speeds that could be delivered when excluding services that are unable to achieve maximum plan speeds',
										data: values['Potential speeds that could be delivered when excluding services that are unable to achieve maximum plan speeds']
									}]									
								})							
							}else if (response['chart_type']=='avg_dl_speed'){
								
								var chart_title = "Average NBN and ADSL download speeds by hour of day (Mbps) for month of "+month;
								$('#note_id').html("");	
								$('#title_id').html("");
								Highcharts.chart(chart_index, {
									chart: {
										type: 'column'
									},
									title: {
										text: chart_title
									},
									xAxis: {
										categories: ['NBN 100/40Mbps', 'NBN 50/20Mbps', 'NBN 25/5Mbps', 'ADSL'],
										crosshair: true
									},
									yAxis: {
										min: 0,
										title: {
											text: 'Download Speed (Mbps)'
										}
									},
									tooltip: {
										headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
										pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
											'<td style="padding:0"><b>{point.y:.1f} Mbps</b></td></tr>',
										footerFormat: '</table>',
										shared: true,
										useHTML: true
									},
									plotOptions: {
												column: {
											pointPadding: 0.2,
											borderWidth: 0
										}
									},
									series: [{
										name: 'Average DL Speed',
										data: values['Average DL Speed']
									}]									
								})							
							}else if (response['chart_type']=='distribution_of_speed'){
								
								var chart_title = "Distribution of download speed tests, by percentage of plan speed for month of "+month;
								$('#note_id').html("");	
								$('#title_id').html("");
								
								Highcharts.chart(chart_index, {
									chart: {
										type: 'column'
									},
									title: {
										text: chart_title
									},
									xAxis: {
										categories: ['Over 90% plan speed', '50% - 90% plan speed', 'Under 50% plan speed'],
										crosshair: true
									},
									yAxis: {
										min: 0,
										title: {
											text: 'Percentage (%)'
										}
									},
									tooltip: {
										headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
										pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
											'<td style="padding:0"><b>{point.y:.1f} %</b></td></tr>',
										footerFormat: '</table>',
										shared: true,
										useHTML: true
									},
									plotOptions: {
												column: {
											pointPadding: 0.2,
											borderWidth: 0
										}
									},
									series: [{
										name: 'Distrubtion_of_speed',
										data: values['Distrubtion_of_speed']
									}]									
								})							
							}else if (response['chart_type']=='packet_loss'){
								
								var chart_title = "Frequency of packet loss for month of "+month;
								$('#note_id').html("");	
								$('#title_id').html("");
								
								Highcharts.chart(chart_index, {
									chart: {
										type: 'column'
									},
									title: {
										text: chart_title
									},
									xAxis: {
										categories: ['0%', '>0% - 0_1%', '0_1% - 0_2%', '0_2% - 0_3%', '0_3% - 0_4%', '0_4% - 0_5%', '0_5% - 0_6%', '0_6% - 0_7%','0_7% - 0_8%', '0_8% - 0_9%', '0_9% - 1%', '>=1%'],
										crosshair: true
									},
									yAxis: {
										min: 0,
										title: {
											text: 'Percentage (%)'
										}
									},
									tooltip: {
										headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
										pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
											'<td style="padding:0"><b>{point.y:.1f} %</b></td></tr>',
										footerFormat: '</table>',
										shared: true,
										useHTML: true
									},
									plotOptions: {
												column: {
											pointPadding: 0.2,
											borderWidth: 0
										}
									},
									series: [{
										name: 'Percentage of tests (%)',
										data: values['Percentage of tests (%)']
									}]									
								})							
							}else if (response['chart_type']=='hour_of_day'){
													
								var chart_title = "Average NBN and ADSL download speeds by hour of day (Mbps) for month of "+month;
								$('#note_id').html("");	
								$('#title_id').html("");
													
								var months_mapping = {'Feb': 2,'Nov':11,'Aug':10,'May':5,'Jul':7,'Mar':3};					
								var month_string = month.substring(0, 3);
								var year_string = month.substring(month.length-4, month.length);
								var year_num=parseInt(year_string);
								var month_num=parseInt(months_mapping[month_string]);
								console.log(month, month_num, year_num);
								
								tier100=new Array();
								tier50=new Array();
								tier25=new Array();
								tierADSL=new Array();
								
								
								$.each(values, function( key, value ) {
									//console.log( key + ": " + value );
									if (key=='100/40 Mbps'){
										$.each(value, function( d_key, d_value ) {
											//console.log(d_key,d_value);
											tier100.push([Date.UTC(year_num, month_num, 1,d_key,0,0), d_value])
										});
									}else if (key=='50/20 Mbps') {
										$.each(value, function( d_key, d_value ) {
											tier50.push([Date.UTC(year_num, month_num, 1,d_key,0,0), d_value])
										});
									}else if (key=='25/5 Mbps') {
										$.each(value, function( d_key, d_value ) {
											tier25.push([Date.UTC(year_num, month_num, 1,d_key,0,0), d_value])
										});
									}else if (key=='ADSL') {
										$.each(value, function( d_key, d_value ) {
											tierADSL.push([Date.UTC(year_num, month_num, 1,d_key,0,0), d_value])
										});
									}
								});
								
								Highcharts.chart(chart_index, {
									chart: {
										type: 'spline'
									},
									title: {
										text: chart_title
									},
									xAxis: {
										type: 'datetime',
										dateTimeLabelFormats: { // don't display the dummy year
											month: '%e. %b, %H:%M:%S',
											year: '%b'
											//hour: '%A, %b %e, %H:%M'
											
										},
										title: {
											text: 'Date'
										}
									},
									yAxis: {
										title: {
											text: 'Download Speed (Mbps)'
										},
										min: 0
									},
									tooltip: {
										headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
										pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
											'<td style="padding:0"><b>{point.y:.1f} Mbps</b></td></tr>',
										footerFormat: '</table>',
										shared: true,
										useHTML: true
									},

									plotOptions: {
										series: {
											marker: {
												enabled: true
											}
										}
									},

									colors: ['#6CF', '#39F', '#06C', '#036', '#000'],

									// Define the data points. All series have a dummy year
									// of 1970/71 in order to be compared on the same x axis. Note
									// that in JavaScript, months start at 0 for January, 1 for February etc.
									series: [{
										name: "100/40 Mbps",
										data: tier100
									}, {
										name: "50/20 Mbps",
										data: tier50
									}, {
										name: "25/5 Mbps",
										data: tier25
									}, {
										name: "ADSL",
										data: tierADSL
									}],

									responsive: {
										rules: [{
											condition: {
												maxWidth: 500
											},
											chartOptions: {
												plotOptions: {
													series: {
														marker: {
															radius: 2.5
														}
													}
												}
											}
										}]
									}
								})						
							}
						
						}else{
								if (response['chart_type']=='speeds' || response['chart_type']=='outages' || response['chart_type']=='webpage_load' || response['chart_type']=='latency'){
									var chart_key =0;		
									var y_axis_string="";
									var tooltip_units="";
									var chart_title="";
																
									if (response['chart_type']=='speeds'){
										y_axis_string='Download Speed (% of Max Plan Speed)';
										tooltip_units='%';
										chart_title="<br> NBN plan speeds delivered during busy hours and the busiest hour over the month of all time";
										$('#title_id').html(chart_title);
									}else if (response['chart_type']=='webpage_load'){
										y_axis_string='Web page loading time (sec)';
										tooltip_units='sec';
										chart_title="<br> Web page loading time (seconds) for month of all time";
										$('#title_id').html(chart_title);
									}else if (response['chart_type']=='latency'){
										y_axis_string='Latency (ms)';
										tooltip_units='ms';
										chart_title="<br> Latency (milliseconds) for month of all time";
										$('#title_id').html(chart_title);
									}
											
									$.each( values, function( category_key, category_value ) {
										console.log(category_key, category_value);
																			
										var chart_index='container'+chart_key.toString();
										
										aussiebroadband=new Array();
										dodo_iprimus=new Array();
										exetel=new Array();
										iinet=new Array();
										myrepublic=new Array();
										optus=new Array();
										telstra=new Array();
										tpg=new Array();											
																				
										$.each(category_value, function(isp_name, isp_value ) {													
																																			
												$.each(isp_value, function(d_key, d_value ) {
																									
													var dates = d_key.split('-');
													var year_num=parseInt(dates[0]);
													var month_num=parseInt(dates[1])-1;
													var day_num=parseInt(dates[2]);
													var value = parseInt(d_value);
													if (isp_name =='0'){
														aussiebroadband.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (isp_name=='1') {
														dodo_iprimus.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (isp_name=='2') {
														exetel.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (isp_name=='3') {
														iinet.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (isp_name=='4') {
														myrepublic.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (isp_name=='5') {
														optus.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (isp_name=='6') {
														telstra.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (isp_name=='7') {
														tpg.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}
												});
																	
										});
										
										Highcharts.chart(chart_index, {
											chart: {
												type: 'spline'
											},
											title: {
												text: category_key
											},
											xAxis: {
												type: 'datetime',
												dateTimeLabelFormats: { // don't display the dummy year
													month: '%Y, %b',
													year: '%b'
													//hour: '%A, %b %e, %H:%M'
													
												},
												title: {
													text: 'Date'
												}
											},
											yAxis: {
												title: {
													text: y_axis_string
												},
												min: 0
											},
											tooltip: {
												headerFormat: '<span style="font-size:10px">{point.x: %b-%Y}</span><table>',
												pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
													'<td style="padding:0"><b>: {point.y:.2f} '+tooltip_units+'</b></td></tr>',
												footerFormat: '</table>',
												shared: true,
												useHTML: true
											},
											plotOptions: {
												series: {
													marker: {
														enabled: true
													}
												}
											},

											colors: ['#6CF', '#39F', '#06C', '#036', '#000'],

											// Define the data points. All series have a dummy year
											// of 1970/71 in order to be compared on the same x axis. Note
											// that in JavaScript, months start at 0 for January, 1 for February etc.
																					
											series: [{
												name: "Aussie Broadband",
												data: aussiebroadband
											}, {
												name: "Dodo & iPrimus",
												data: dodo_iprimus
											}, {
												name: "Exetel",
												data: exetel
											}, {
												name: "iiNet",
												data: iinet
											}, {
												name: "MyRepublic",
												data: myrepublic
											}, {
												name: "Optus",
												data: optus
											}, {
												name: "Telstra",
												data: telstra
											}, {
												name: "TPG",
												data: tpg
											}],

											responsive: {
												rules: [{
													condition: {
														maxWidth: 500
													},
													chartOptions: {
														plotOptions: {
															series: {
																marker: {
																	radius: 2.5
																}
															}
														}
													}
												}]
											}
										})											
										
										chart_key++;
										
									});
									
								} else if (response['chart_type']=='technology - nbn'){
									var chart_key =0;		
									chart_title="<br> NBN and ADSL plan speeds delivered during busy hours by technology for month of all time";
									$('#title_id').html(chart_title);
																											
									$.each( values, function( category_key,category_value ) {
										console.log(category_key, category_value);
										var chart_index='container'+chart_key.toString();
										chart_title=chart_title+':'+category_key;
										
										FTTP_list=new Array();
										FTTN_list=new Array();
										HFC_list=new Array();
										FTTC_list=new Array();										
										
										$.each(category_value, function(tech_key, tech_value ) {
												var chart_index='container'+category_key.toString()
												
												$.each(tech_value, function(d_key, d_value ) {
													//console.log(d_key, d_value);
													var dates = d_key.split('-');
													var year_num=parseInt(dates[0]);
													var month_num=parseInt(dates[1])-1;
													var day_num=parseInt(dates[2]);
													var value = parseInt(d_value);
													if (tech_key =='0'){
														FTTP_list.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (tech_key=='1') {
														FTTN_list.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (tech_key=='2') {
														HFC_list.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (tech_key=='3') {
														FTTC_list.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}
												});
																						
										});
										
												//console.log(FTTP_list);
												
												Highcharts.chart(chart_index, {
													chart: {
														type: 'spline'
													},
													title: {
														text: category_key
													},
													xAxis: {
														type: 'datetime',
														dateTimeLabelFormats: { // don't display the dummy year
															month: '%Y, %b',
															year: '%b'
															//hour: '%A, %b %e, %H:%M'
															
														},
														title: {
															text: 'Date'
														}
													},
													yAxis: {
														title: {
															text: 'Percentage of Maximum Speed (%)'
														},
														min: 0
													},
													tooltip: {
														headerFormat: '<span style="font-size:10px">{point.x: %b-%Y}</span><table>',
														pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
															'<td style="padding:0"><b>: {point.y:.2f} %</b></td></tr>',
														footerFormat: '</table>',
														shared: true,
														useHTML: true
													},

													plotOptions: {
														series: {
															marker: {
																enabled: true
															}
														}
													},

													colors: ['#6CF', '#39F', '#06C', '#036', '#000'],

													// Define the data points. All series have a dummy year
													// of 1970/71 in order to be compared on the same x axis. Note
													// that in JavaScript, months start at 0 for January, 1 for February etc.
																							
													series: [{
														name: "FTTP",
														data: FTTP_list
													}, {
														name: "FTTN",
														data: FTTN_list
													}, {
														name: "HFC",
														data: HFC_list
													}, {
														name: "FTTC",
														data: FTTC_list
													}],

													responsive: {
														rules: [{
															condition: {
																maxWidth: 500
															},
															chartOptions: {
																plotOptions: {
																	series: {
																		marker: {
																			radius: 2.5
																		}
																	}
																}
															}
														}]
													}
												})		

										chart_key++;		
									
									});									
									
								} else if (response['chart_type']=='avg_dl_speed'){
									var chart_key =0;	
									var chart_title="<br> Average NBN and ADSL download speeds by hour of day (Mbps) for month of all time";
									$('#title_id').html(chart_title);
									
									NBN_100=new Array();
									NBN_50=new Array();
									NBN_25=new Array();
									ADSL=new Array();
																	
									$.each( values, function( category_key, category_value ) {
										console.log(category_key, category_value);
										var chart_index='container'+chart_key.toString();
										chart_title=chart_title+':'+category_key;
										
										$.each(category_value, function(plan_key, plan_value ) {
												
												$.each(plan_value, function(d_key, d_value ) {
													//console.log(d_key, d_value);
													var dates = d_key.split('-');
													var year_num=parseInt(dates[0]);
													var month_num=parseInt(dates[1])-1;
													var day_num=parseInt(dates[2]);
													var value = parseInt(d_value);
													if (plan_key =='0'){
														NBN_100.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (plan_key=='1') {
														NBN_50.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (plan_key=='2') {
														NBN_25.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (plan_key=='3') {
														ADSL.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}
												});												
										
											});
											
											//console.log(FTTP_list);
											
											Highcharts.chart(chart_index, {
												chart: {
													type: 'spline'
												},
												title: {
													text: category_key
												},
												xAxis: {
													type: 'datetime',
													dateTimeLabelFormats: { // don't display the dummy year
														month: '%Y, %b',
														year: '%b'
														//hour: '%A, %b %e, %H:%M'
														
													},
													title: {
														text: 'Date'
													}
												},
												yAxis: {
													title: {
														text: 'Download Speed (Mbps)'
													},
													min: 0
												},
												tooltip: {
													headerFormat: '<span style="font-size:10px">{point.x: %b-%Y}</span><table>',
													pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
														'<td style="padding:0"><b>: {point.y:.2f} Mbps</b></td></tr>',
													footerFormat: '</table>',
													shared: true,
													useHTML: true
												},

												plotOptions: {
													series: {
														marker: {
															enabled: true
														}
													}
												},

												colors: ['#6CF', '#39F', '#06C', '#036', '#000'],

												// Define the data points. All series have a dummy year
												// of 1970/71 in order to be compared on the same x axis. Note
												// that in JavaScript, months start at 0 for January, 1 for February etc.
																						
												series: [{
													name: "NBN 100/40Mbps",
													data: NBN_100
												}, {
													name: "NBN 50/20Mbps",
													data: NBN_50
												}, {
													name: "NBN 25/5Mbps",
													data: NBN_25
												}, {
													name: "ADSL",
													data: ADSL
												}],

												responsive: {
													rules: [{
														condition: {
															maxWidth: 500
														},
														chartOptions: {
															plotOptions: {
																series: {
																	marker: {
																		radius: 2.5
																	}
																}
															}
														}
													}]
												}
											})

										chart_index++;
									
									});										
									
								} else if (response['chart_type']=='distribution_of_speed'){
									var chart_key =0;
									var chart_title="<br>Distribution of download speed tests, by percentage of plan speed for month of all time";
									$('#title_id').html(chart_title);
																									
									$.each( values, function( category_key, category_value ) {
										var chart_index='container'+chart_key.toString();
										over_90=new Array();
										over_50=new Array();
										under_50=new Array();										
										
										$.each(category_value, function(threshold_key, threshold_value ) {
												$.each(threshold_value, function(d_key, d_value ) {
													//console.log(d_key, d_value);
													var dates = d_key.split('-');
													var year_num=parseInt(dates[0]);
													var month_num=parseInt(dates[1])-1;
													var day_num=parseInt(dates[2]);
													var value = parseInt(d_value);
													if (threshold_key =='0'){
														over_90.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (threshold_key=='1') {
														over_50.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (threshold_key=='2') {
														under_50.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}
												});
																						
											});
											

											//console.log(FTTP_list);
											
											Highcharts.chart(chart_index, {
												chart: {
													type: 'spline'
												},
												title: {
													text: category_key
												},
												xAxis: {
													type: 'datetime',
													dateTimeLabelFormats: { // don't display the dummy year
														month: '%Y, %b',
														year: '%b'
														//hour: '%A, %b %e, %H:%M'
														
													},
													title: {
														text: 'Date'
													}
												},
												yAxis: {
													title: {
														text: 'Percentage (%)'
													},
													min: 0
												},
												tooltip: {
													headerFormat: '<span style="font-size:10px">{point.x: %b-%Y}</span><table>',
													pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
														'<td style="padding:0"><b>: {point.y:.2f} %</b></td></tr>',
													footerFormat: '</table>',
													shared: true,
													useHTML: true
												},

												plotOptions: {
													series: {
														marker: {
															enabled: true
														}
													}
												},

												colors: ['#6CF', '#39F', '#06C', '#036', '#000'],

												// Define the data points. All series have a dummy year
												// of 1970/71 in order to be compared on the same x axis. Note
												// that in JavaScript, months start at 0 for January, 1 for February etc.
																						
												series: [{
													name: "Over 90% plan speed",
													data: over_90
												}, {
													name: "50% - 90% plan speed",
													data: over_50
												}, {
													name: "Under 50% plan speed",
													data: under_50
												}],

												responsive: {
													rules: [{
														condition: {
															maxWidth: 500
														},
														chartOptions: {
															plotOptions: {
																series: {
																	marker: {
																		radius: 2.5
																	}
																}
															}
														}
													}]
												}
											})													
									
										chart_index++;
									});										
								
								} else if (response['chart_type']=='packet_loss'){
									
									var chart_title="<br>Frequency of packet loss for month of all time";
									$('#title_id').html(chart_title);
									
									var chart_key =0;

									margin_0=new Array();
									margin_1=new Array();
									margin_2=new Array();
									margin_3=new Array();
									margin_4=new Array();
									margin_5=new Array();
									margin_6=new Array();
									margin_7=new Array();
									margin_8=new Array();
									margin_9=new Array();
									margin_10=new Array();
									margin_11=new Array();
																	
									$.each( values, function( category_key, category_value ) {
										console.log(category_key, category_value);
										var chart_index='container'+chart_key.toString();
										chart_title=chart_title+':'+category_key;
										
										$.each(category_value, function(margin_key, margin_value ) {
												
												$.each(margin_value, function(d_key, d_value ) {
													//console.log(d_key, d_value);
													var dates = d_key.split('-');
													var year_num=parseInt(dates[0]);
													var month_num=parseInt(dates[1])-1;
													var day_num=parseInt(dates[2]);
													var value = parseInt(d_value);
													if (margin_key =='0'){
														margin_0.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (margin_key=='1') {
														margin_1.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (margin_key=='2') {
														margin_2.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (margin_key=='3') {
														margin_3.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (margin_key=='4') {
														margin_4.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (margin_key=='5') {
														margin_5.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (margin_key=='6') {
														margin_6.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (margin_key=='7') {
														margin_7.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (margin_key=='8') {
														margin_8.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (margin_key=='9') {
														margin_9.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (margin_key=='10') {
														margin_10.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}else if (margin_key=='11') {
														margin_11.push([Date.UTC(year_num, month_num, day_num,0,0,0), value])
													}
												});
																						
											});
										
												//console.log(FTTP_list);
												
												Highcharts.chart(chart_index, {
													chart: {
														type: 'spline'
													},
													title: {
														text: category_key
													},
													xAxis: {
														type: 'datetime',
														dateTimeLabelFormats: { // don't display the dummy year
															month: '%Y, %b',
															year: '%b'
															//hour: '%A, %b %e, %H:%M'
															
														},
														title: {
															text: 'Date'
														}
													},
													yAxis: {
														title: {
															text: 'Percentage (%)'
														},
														min: 0
													},
													tooltip: {
														headerFormat: '<span style="font-size:10px">{point.x: %b-%Y}</span><table>',
														pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
															'<td style="padding:0"><b>: {point.y:.2f} %</b></td></tr>',
														footerFormat: '</table>',
														shared: true,
														useHTML: true
													},

													plotOptions: {
														series: {
															marker: {
																enabled: true
															}
														}
													},

													colors: ['#6CF', '#39F', '#06C', '#036', '#000'],

													// Define the data points. All series have a dummy year
													// of 1970/71 in order to be compared on the same x axis. Note
													// that in JavaScript, months start at 0 for January, 1 for February etc.
																							
													series: [{
														name: "0%",
														data: margin_0
													}, {
														name: ">0% - 0_1%",
														data: margin_1
													}, {
														name: "0_1% - 0_2%",
														data: margin_2
													}, {
														name: "0_2% - 0_3%",
														data: margin_3
													}, {
														name: "0_3% - 0_4%",
														data: margin_4
													}, {
														name: "0_4% - 0_5%",
														data: margin_5
													}, {
														name: "0_5% - 0_6%",
														data: margin_6
													}, {
														name: "0_6% - 0_7%",
														data: margin_7
													}, {
														name: "0_7% - 0_8%",
														data: margin_8
													}, {
														name: "0_8% - 0_9%",
														data: margin_9
													}, {
														name: "0_9% - 1%",
														data: margin_10
													}, {
														name: ">=1%",
														data: margin_11
													}],

													responsive: {
														rules: [{
															condition: {
																maxWidth: 500
															},
															chartOptions: {
																plotOptions: {
																	series: {
																		marker: {
																			radius: 2.5
																		}
																	}
																}
															}
														}]
													}
												})	

											chart_key++;
									
									});									
																										
								}else if (response['chart_type']=='hour_of_day'){
									
									var chart_title="<br>Average NBN and ADSL download speeds by hour of day (Mbps) for month of all time";
									$('#title_id').html(chart_title);
									
									var chart_count=0;
									$.each(values, function( key, value ) {
										//var months_mapping = {'Feb': 2,'Nov':11,'Aug':10,'May':5,'Jul':7,'Mar':3};					
										//var month_string = month.substring(0, 3);
										//var year_string = month.substring(month.length-4, month.length);
										var year_num=2020;
										var month_num=4
										console.log(key, value, month_num, year_num);
										var chart_index='container'+chart_count.toString();
										Feb2020=new Array();
										Nov2019=new Array();
										Aug2019=new Array();
										May2019=new Array();
										Feb2019=new Array();
										Nov2018=new Array();
										July2018=new Array();
										March2018=new Array();											
										
										$.each(value, function(category_key, category_value ) {
											console.log(category_key,category_value);
																		
											//console.log( key + ": " + value );
											if (category_key=='0'){
												$.each(category_value, function( d_key, d_value ) {
													//console.log(d_key,d_value);
													day_num=parseInt(d_key.split(':')[0])
													Feb2020.push([Date.UTC(year_num, month_num, 1,day_num,0,0), d_value])
												});
											}else if (category_key=='1') {
												$.each(category_value, function( d_key, d_value ) {
													day_num=parseInt(d_key.split(':')[0])
													Nov2019.push([Date.UTC(year_num, month_num, 1,day_num,0,0), d_value])
												});
											}else if (category_key=='2') {
												$.each(category_value, function( d_key, d_value ) {
													day_num=parseInt(d_key.split(':')[0])
													Aug2019.push([Date.UTC(year_num, month_num, 1,day_num,0,0), d_value])
												});
											}else if (category_key=='3') {
												$.each(category_value, function( d_key, d_value ) {
													day_num=parseInt(d_key.split(':')[0])
													May2019.push([Date.UTC(year_num, month_num, 1,day_num,0,0), d_value])
												});
											}else if (category_key=='4') {
												$.each(category_value, function( d_key, d_value ) {
													day_num=parseInt(d_key.split(':')[0])
													Feb2019.push([Date.UTC(year_num, month_num, 1,day_num,0,0), d_value])
												});
											}else if (category_key=='5') {
												$.each(category_value, function( d_key, d_value ) {
													day_num=parseInt(d_key.split(':')[0])
													Nov2018.push([Date.UTC(year_num, month_num, 1,day_num,0,0), d_value])
												});
											}else if (category_key=='6') {
												$.each(category_value, function( d_key, d_value ) {
													day_num=parseInt(d_key.split(':')[0])
													July2018.push([Date.UTC(year_num, month_num, 1,day_num,0,0), d_value])
												});
											}else if (category_key=='7') {
												$.each(category_value, function( d_key, d_value ) {
													day_num=parseInt(d_key.split(':')[0])
													March2018.push([Date.UTC(year_num, month_num, 1,day_num,0,0), d_value])
												});
											}
										
										});
										
										Highcharts.chart(chart_index, {
											chart: {
												type: 'spline'
											},
											title: {
												text: key
											},
											xAxis: {
												type: 'datetime',
												dateTimeLabelFormats: { // don't display the dummy year
													month: '%e. %b, %H:%M:%S',
													year: '%b'
													//hour: '%A, %b %e, %H:%M'
													
												},
												title: {
													text: 'Date'
												}
											},
											yAxis: {
												title: {
													text: 'Download Speed (Mbps)'
												},
												min: 0
											},
											tooltip: {
												headerFormat: '<span style="font-size:10px">{point.x: %b-%Y}</span><table>',
												pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
													'<td style="padding:0"><b>: {point.y:.2f} Mbps</b></td></tr>',
												footerFormat: '</table>',
												shared: true,
												useHTML: true
											},
											plotOptions: {
												series: {
													marker: {
														enabled: true
													}
												}
											},

											colors: ['#6CF', '#39F', '#06C', '#036', '#000'],

											// Define the data points. All series have a dummy year
											// of 1970/71 in order to be compared on the same x axis. Note
											// that in JavaScript, months start at 0 for January, 1 for February etc.
											series: [{
												name: "Feb2020",
												data: Feb2020
											}, {
												name: "Nov2019",
												data: Nov2019
											}, {
												name: "Aug2019",
												data: Aug2019
											}, {
												name: "May2019",
												data: May2019
											}, {
												name: "Feb2019",
												data: Feb2019
											}, {
												name: "Nov2018",
												data: Nov2018
											}, {
												name: "July2018",
												data: July2018
											}, {
												name: "March2018",
												data: March2018
											}],

											responsive: {
												rules: [{
													condition: {
														maxWidth: 500
													},
													chartOptions: {
														plotOptions: {
															series: {
																marker: {
																	radius: 2.5
																}
															}
														}
													}
												}]
											}
										})	
										
									chart_count++;	

								});
							
							}
				
						}						
						
					});	
						
						count++;
									
			},
			error: function(xhr, textStatus, errorThrown) {
				alert("ERROR!!!  Contact Administrator.  Message: "+errorThrown);
			}
		});
	}); 
});
 </script>

</head> 
  <body> 
	<?php echo $html_display; ?>
	
	<div id='title_id' align='center' style="font-family:arial;font-size:25px;"></div>
	<figure class="highcharts-figure">
		<div id="container0"></div>
		<div id="container1"></div>
		<div id="container2"></div>
		<div id="container3"></div>
		<div id="container4"></div>
	</figure>	
	<div id='note_id' align='center' style="font-family:arial;font-size:20px;"></div>
	<br> Contact Site Creator: <a href='mailto:rommel.poggenberg@gmail.com'>Rommel Poggenberg</a> 
  </body>
</html>