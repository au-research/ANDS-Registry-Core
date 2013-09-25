<?php 
/**
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<input type="hidden" value="<?php echo $data_source['id'];?>" id="data_source_id"/>
<div id="content" style="margin-left:0px">
	<div id="content-header">
		<h1>Manage My Record</h1>
		<div class="btn-group">
			<a class="btn btn-large" title="Manage Files"><i class="icon-file"></i></a>
		</div>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('registry_object/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home'))?>
		<a href="#" class="current"><?php echo $data_source['title'];?></a>
		<div style="float:right">
			<a>Selected <b>3</b> / 146</a>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12 center" style="text-align: center;">					
				<ul class="stat-boxes">
					<li>
						<div class="right">
							<strong><?php echo $data_source['count_total'];?></strong>
							Records
						</div>
					</li>
					<li class="ds_filter" type="status" _value="APPROVED">
						<div class="right peity_bar_good">
							<strong><?php echo $data_source['count_APPROVED'];?></strong>
							APPROVED
						</div>
					</li>
					<li class="ds_filter" type="status" _value="SUBMITTED_FOR_ASSESSMENT">
						<div class="right peity_bar_good">
							<strong><?php echo $data_source['count_SUBMITTED_FOR_ASSESSMENT'];?></strong>
							Submitted
						</div>
					</li>
					<li>
						<div class="right peity_bar_good">
							<strong><?php echo $data_source['count_PUBLISHED'];?></strong>
							Published
						</div>
					</li>
					<li>
						<div class="right peity_bar_bad">
							<strong>0</strong>
							Errors
						</div>
					</li>
				</ul>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<div class="widget-box">
					<div class="widget-title">
						<h5>SOME All Registry Objects</h5>
					</div>
					<div class="widget-content nopadding">
						<table class="table table-bordered data-table" id="record_table">
							<thead>
								<tr>
									<th>id</th>
									<th>Title</th>
									<th>Status</th>
									<th>Options</th>
								</tr>
							</thead>
							<tbody>
								

							</tbody>
						</table>  
					</div>
				</div>
			</div>
		</div>
		
	</div>
</div>
<?php $this->load->view('footer');?>