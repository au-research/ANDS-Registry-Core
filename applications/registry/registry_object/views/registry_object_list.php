<?php 
/**
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<div id="content" style="margin-left:0px">
	<div class="content-header">
		<h1><?php echo $list_title;?></h1>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home'))?>
		<a href="#" class="current"><?php echo $list_title;?></a>
	</div>

	<div class="container-fluid">
		<div class="row-fluid">
			<div class="widget-box ro_box">
				<div class="widget-title stick"><h5><?php echo $list_title;?></h5></div>
				<div class="widget-content nopadding">
					<ul class="sortable">
						<?php if (!is_array($ros)): ?> 
							<li><div class="ro_content">&nbsp; &nbsp;<strong> No records to display&hellip;</strong></div></li>
						<?php else: ?>
							<?php foreach($ros as $ro):?>
							<li>
								<div class="ro_item_header">
									<div class="ro_title"><?php echo anchor('registry_object/view/'.$ro->id, $ro->title);?></div>
								</div>
								
								<div class="ro_content">
									<p>
										<span class="tag" tip="Last Modified"><i class="icon icon-time"></i><?php echo date("j F Y, g:i a", (int)$ro->getAttribute('updated')); ?></span>
										<img class="tag" tip="<?php echo $ro->class;?>" src="<?php echo asset_url('img/'.$ro->class.'.png', 'base');?>"/>
										<span class="tag gold_status_flag" tip="<h5>Gold Standard</h5><p>The following record has been verified<br/> as an exemplary record <br/>by the ANDS Metadata Assessment Group.</p>"><i class="icon icon-star-empty"></i></span>
									</p>
								</div>
								<div class="btn-group btn-group-vertical right-menu hide">
									<button class="contextmenu btn btn-small" status="{{name}}"><i class="icon icon-wrench"></i></button>
									<button class="tipTag btn btn-small" ro_id="{{id}}"><i class="icon icon-tag"></i></button>
								</div>
								<div class='clearfix'></div>
							</li>
							<?php endforeach;?>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>
		
	</div>
</div>
<?php $this->load->view('footer');?>