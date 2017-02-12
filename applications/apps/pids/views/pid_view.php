<?php $this->load->view('header');?>
<div class="content-header">
	<h1><?php echo $pid['handle'];?></h1>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
	<?php echo anchor('/pids', 'Identify My Data'); ?>
	<?php echo anchor('/pids/view/?handle='.$pid['handle'], $pid['handle'], array('class'=>'current')); ?>
</div>
<div class="container-fluid" id="main-content">
	<div class="row-fluid">
		<div class="span2">&nbsp;</div>
		<div class="span8">
			<div class="widget-box">
				<div class="widget-title">
					<h5><?php echo $pid['handle'];?></h5>
				</div>
				<div class="widget-content">
					<dl>
						<dt>Resolver Link</dt>
						<dd><?php echo '<a href="'.$resolver_url.'">'.$resolver_url.'</a>' ?></dd>
						<?php if(isset($pid['desc']) && sizeof($pid['desc'])>0): ?>
							<dt>Description</dt>
							<?php foreach($pid['desc'] as $key=>$value): ?>
							<dd><?php echo $value; ?></dd>
							<?php endforeach; ?>
						<?php endif; ?>
						<?php if(isset($pid['url']) && sizeof($pid['url'])>0): ?>
							<dt>URL</dt>
							<?php foreach($pid['url'] as $key=>$value): ?>
							<dd><?php echo $value; ?></dd>
							<?php endforeach; ?>
						<?php endif; ?>
					</dl>
					<a data-toggle="modal" href="#edit_modal" href="javascript:;" class="btn btn-primary">Edit</a>
					<a href="javascript:;" class="btn btn-link" id="reassign_toggle">Re-assign Ownership</a>
					<span id="reassign">
						<hr/>
						Re-assign the ownership of this handle to: 
						<select name="" id="reassign_value" class="chosen">
							<?php foreach($pid_owners as $a): ?>
							<option value="<?php echo $a['handle']; ?>"><?php echo $a['identifier']; ?></option>
							<?php endforeach; ?>
						</select>
						<a href="javascript:;" class="btn btn-primary" id="confirm_reassign" handle="<?php echo $pid['handle']; ?>">Confirm</a>
					</span>

				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal hide fade" id="delete_modal">
	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3><?php echo $pid['handle'] ?></h3>
	</div>
	
	<div class="modal-screen-container">
		<div class="modal-body">
			<div class="alert alert-error">
				Are you sure you want to delete this PID? This is irreversible
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<a id="delete_confirm" href="javascript:;" class="btn btn-primary" data-loading-text="Deleting...">Proceed</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
</div>

<div class="modal hide fade" id="edit_modal">
	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3><?php echo $pid['handle'] ?></h3>
	</div>
	
	<div class="modal-screen-container">
		<div class="modal-body">
			<div class="alert alert-info">
				Please provide the relevant information
			</div>
			<form action="#" method="get" class="form-horizontal" id="edit_form">
				<?php foreach($pid['desc'] as $idx=>$desc):?>
				<div class="control-group">
					<label class="control-label">Description</label>
					<div class="controls">
						<input type="text" name="desc" value="<?php echo $desc; ?>" idx="<?php echo $idx; ?>" changed="false"/>
					</div>
				</div>
				<?php endforeach; ?>
				<?php foreach($pid['url'] as $idx=>$url):?>
				<div class="control-group">
					<label class="control-label">URL</label>
					<div class="controls">
						<input type="text" name="url" value="<?php echo $url; ?>" idx="<?php echo $idx; ?>" changed="false"/>
					</div>
				</div>
				<?php endforeach; ?>
				<div id="separate_line"></div>
				<div class="control-group">
					<div class="controls">
						<a href="javascript:;" class="btn btn-primary add_new" add-type="url"><i class="icon icon-plus"></i> Add URL</a>
						<a href="javascript:;" class="btn btn-primary add_new" add-type="desc"><i class="icon icon-plus"></i> Add Description</a>
					</div>
				</div>
			</form>
			<div class="control-group hide" id="new_desc">
				<label class="control-label">Description</label>
				<div class="controls">
					<input type="text" name="desc" value="" changed="false"/>
				</div>
			</div>
			<div class="control-group hide" id="new_url">
				<label class="control-label">URL</label>
				<div class="controls">
					<input type="text" name="url" value="" changed="false"/>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<div class="progress progress-striped active hide" id="progress-bar"></div>
		<a id="update_confirm" href="javascript:;" class="btn btn-primary" data-loading-text="Updating..." handle="<?php echo $pid['handle']; ?>">Update</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
</div>

<?php $this->load->view('footer');?>