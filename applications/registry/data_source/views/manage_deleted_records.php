<?php 
/**
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<input type="hidden" value="<?php echo $ds->id;?>" id="data_source_id"/>
<div id="content" style="margin-left:0px">
	<div class="content-header">
		<h1><?php echo $ds->title;?> <small>Deleted Records</small></h1>
		<ul class="nav nav-pills">
			<li class=""><?php echo anchor('data_source/manage#!/view/'.$ds->id,'Dashboard');?></li>
			<li class=""><?php echo anchor('data_source/manage_records/'.$ds->id,'Manage Records');?></li>
			<li class=""><?php echo anchor('data_source/report/'.$ds->id,'Reports');?></li>
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
				<?php
					if($record_count == 0){
						echo "No recoverable records were found. Only records which have previously been PUBLISHED are able to be recovered.  <a href='javascript:window.history.back();'>Go Back</a>.";  
					}else{
						echo "<b>".$record_count."</b> recoverable record".($record_count!=1 ? "s" : "")." found."; 
					}					
				 foreach($deleted_records as $key=>$history):?>
					<div class="widget-box">
						<div class="widget-title"><h5><?php echo 'Deleted Key: '.$key;?></h5></div>
						<div class="widget-content nopadding">
							<ul class="activity-list">
								<?php foreach($history as $id=>$r):?>
									<li>
										<a style="color:#0088cc;" href="#">
											<?php echo $r['title']." <span class='label'>(deleted on:" .$r['deleted_date'].")</span>"?>
										</a>
										<div class="more hide" style="padding:5px">
											<div class="btn-group">
												<?php echo "<button class='btn viewrecord' record_key='".$r['id']."'><i class='icon icon-eye-open'></i> View RIF-CS</button>";?>								
												<?php echo "<button class='btn undelete_record' record_key='".$r['id']."'><i class='icon-download-alt'></i> Reinstate this Record</button>";?>
											</div>
											<?php echo "<div class='hide' id='".$r['id']."'>".htmlentities($r['record_data'])."</div>";?>
										</div>
									</li>
								<?php endforeach;?>
							</ul>
						</div>
					</div>
				<?php endforeach;?>
				<div class="pagination alternate">
				<ul>
					<?php
						if($offset==0){
				  		echo '<li class="disabled"><a href="javascript:;">Previous</a></li>';
				  	}else{
				  		$prev = $offset - $limit;
				  		echo '<li>'.anchor('data_source/manage_deleted_records/'.$ds->id.'/'.$prev.'/'.$limit, 'Previous').'</li>';
				  	}
				  ?>
				  <?php
				  	if($record_count<=$offset+$limit){
				  		echo '<li class="disabled"><a href="javascript:;">Next</a></li>';
				  	}else{
				  		$next = $offset + $limit;
				  		echo '<li>'.anchor('data_source/manage_deleted_records/'.$ds->id.'/'.$next.'/'.$limit, 'Next').'</li>';
				  	}
				  ?>
				</ul>
				</div>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="modal hide" id="myModal">
  	<div class="modal-header">
    	<button type="button" class="close" data-dismiss="modal">×</button>
    	<h3>RIF-CS</h3>
  	</div>
  	<div class="modal-body" style="max-height:300px !important;"></div>				 
  			<!-- A hidden loading screen -->
	<div name="resultScreen" class="modal-body hide loading" style="max-height:60%;"></div>
  	<div class="modal-footer">
  		<button class='btn btn-small undelete_record' record_key=''><i class='icon-download-alt'></i> Reinstate this record</button>
  	</div>
</div>


<div class="hide" id="import-screen-success-report-template">
	<div class="alert alert-success">
		{{message}}
	</div>
	
	{{#log}}
		<pre class="well linenums">{{log}}</pre>
	{{/log}}
</div>

<?php $this->load->view('footer');?>