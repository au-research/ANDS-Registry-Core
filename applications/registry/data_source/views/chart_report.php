<?php 
/**
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<input type="hidden" value="<?php echo $ds->id;?>" id="data_source_id"/>
<div id="content" style="margin-left:0px">
	<div class="content-header">
		<h1><?php echo $ds->title;?></h1>
		<ul class="nav nav-pills">
			<li class=""><?php echo anchor('data_source/manage#!/view/'.$ds->id,'Dashboard');?></li>
			<li class=""><?php echo anchor('data_source/manage_records/'.$ds->id,'Manage Records');?></li>
			<li class="active"><?php echo anchor('data_source/report/'.$ds->id,'Reports');?></li>
			<li class=""><?php echo anchor('data_source/manage#!/settings/'.$ds->id,'Settings');?></li>
		</ul>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home'))?>
		<?php echo anchor('data_source/manage/', 'Manage My Data Sources');?>
		<?php echo anchor('data_source/manage#!/view/'.$ds->id, $ds->title.' - Dashboard');?>
		<a href="#" class="current"><?php echo $title;?></a>
	</div>

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<center>
					<h5>Data Source Reports for <?=$ds->title;?> filtered by status: 
					<select id="quality_report_status_dropdown">
					  	<option value="">All Records</option>
						<?php foreach($status_tabs as $status=>$label):?>
						    <option value="<?=$status;?>"><?=$label;?></option>
						<?php endforeach;?>
					</select></h5>
				</center>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<div class="box">

					<div class="pull-right">
						<span class="label"><i class="icon-question-sign icon-white"></i> <a target="_blank" style="color:white;" href="http://ands.org.au/resource/metadata-content-requirements.html#qualitylevels">Quality Level Definitions</a></span>				  
					</div>

					<h4><a id="download_report_link" data-default-href="<?=base_url('data_source/charts/getDataSourceQualityChart/'.$ds->id.'/');?>" href="<?=base_url('data_source/charts/getDataSourceQualityChart/'.$ds->id.'/ALL/true');?>" title="Download Excel Report"><img src="<?=asset_url('img/excel.png','base');?>" /></a> 
						Record Quality Overview <small>(<a id="detailed_report_link" data-default-href="<?=base_url('data_source/quality_report/'.$ds->id);?>" href="<?=base_url('data_source/quality_report/'.$ds->id);?>">view detailed quality report</a>)</small></h4>
					<div id="quality_status_legend" class="chart-legend">

					</div>
					<div id="overall_chart_div" style="width:80%; margin:auto; min-height:250px;">
						<i>Loading data source quality information...</i>
					</div>





				</div>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<div class="box">

					<h4><a href="<?=base_url('data_source/charts/getDataSourceStatusChart/'.$ds->id.'/csv');?>" title="Download Excel Report"><img src="<?=asset_url('img/excel.png','base');?>" /></a> 
						Record Status Overview</h4>
					
					<div id="status_charts">
					</div>

				</div>
			</div>
		</div>	

		<div class="clearfix"></div>

	</div>


<?php $this->load->view('footer');?>