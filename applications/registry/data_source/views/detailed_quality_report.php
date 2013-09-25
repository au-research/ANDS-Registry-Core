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
		<?php echo anchor('data_source/manage#!/view/'.$ds->id, $ds->title. ' - Dashboard');?>
		<a href="#" class="current"><?php echo $title;?></a>
	</div>

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				
				<div class="alert" id="printButtonWarning">
				  Printing this report may contain many pages.
				  <a href="javascript:window.print()" id="printButton" class="btn pull-right btn-small text-center btn-inverse">Print this Report <i class="icon-white icon-print"></i></a>
				</div>

				<?php if(isset($filter)): ?>
				<h3>
				  <strong><?=$filter;?></strong>
				</h3>
				<?php endif; ?>

				<small>
					<span class="label">This report indicates which recommended/required fields are missing from your records.</span>
				</small>

				<?php if(!$report) { echo "<i>No records to display</i>"; } ?>
				<?php foreach($report as $id=>$r):?>
					<div class="widget-box quality_div">
						<div class="widget-title">
							<span class="label label-inverse pull-right"><?php echo $r['status'];?></span> <span class="label label-inverse pull-right">Quality Level: <?php echo $r['quality_level']; ?></span>  <span class="label label-inverse pull-right"><?php echo ucfirst($r['class']); ?></span>
							<h4>
								<?php echo $r['title'];?>
							</h4>
						</div>
						<div class="widget-content print_quality_report quality_report">
							<?php echo $r['report'];?>
							<div class="btn-group">
								<?php echo anchor('registry_object/view/'.$r['id'], '<i class="icon-eye-open"></i> View', array('class'=>'btn'));?>
								<?php echo anchor('registry_object/edit/'.$r['id'], '<i class="icon-edit"></i> Edit', array('class'=>'btn'));?>
							</div>
						</div>
					</div>
				<?php endforeach;?>

			</div>
		</div>
		<div class="clearfix"></div>
	</div>
</div>


<?php $this->load->view('footer');?>