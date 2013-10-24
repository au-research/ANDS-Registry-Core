<?php 

/**
 * Core Maintenance Dashboard
 *  
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/vocab_services/_vocab
 * @package ands/vocab_services
 * 
 */
?>

<?php  $this->load->view('header');?>
<style>
#overall_chart_div
{
	width:80%;
	margin:auto; 
	min-height:250px;
}

#status_charts
{
	display:inline-block;
	margin:auto;
	width:90%;
}

.status_report_chart
{
	min-width:300px;
	display:block;
	width:23%;
	margin: 25px 10px;
	min-height:300px;
	float:left;
}

.chart-legend 
{
	font-size: 0.8em;
	margin-left:20px;
}

.legend-icon
{
	display: inline-block;
	height:10px;
	min-width: 10px;
	width:10px;
	margin-left:14px;
	margin-right:3px;
}

.widget-title
{
	padding-left:12px;
	padding-right:12px;
	height:100%;
	
}
</style>

<div id="content" style="margin-left:0px">


	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<center>
					<h5>Data Source Reports for  all data sources: 
					</h5>

				</center>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<div class="box">

 					<div class="pull-right">
						<span class="label"><i class="icon-question-sign icon-white"></i> <a target="_blank" style="color:white;" href="http://ands.org.au/resource/metadata-content-requirements.html#qualitylevels">Quality Level Definitions</a></span>				  
					</div>
					<div id="quality_status_legend" class="chart-legend">

					</div>
					<?php
					foreach($dataSources as $datasource)
					{
						echo $datasource['chart_html'];
					} 
					?>

				</div>
			</div>
		</div>

<?php $this->load->view('footer');?>