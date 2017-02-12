<?php 

/**
 * Editting Role Interface
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<div class="content-header">
	<h1>Edit Role - <?php echo $role->name;?></h1>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
	<?php echo anchor('/role', 'List Roles'); ?>
	<?php echo anchor('role/view/?role_id='.rawurlencode($role->role_id), $role->name); ?>
	<?php echo anchor('/role/edit/'.rawurlencode($role->role_id), 'Edit Role',array('class'=>'current'));?>
</div>
<div class="container-fluid">
	<div class="row-fluid">

		<div class="span3"></div>
		<div class="span6">
			<div class="widget-box">
				<div class="widget-title"><h5>Edit Role - <?php echo $role->name;?></h5></div>
				<div class="widget-content">
					<form action="?posted=true" method="post" class="form-horizontal">
						<div class="control-group">
							<label for="" class="control-label">ID</label>
							<div class="controls"><span class="uneditable-input"><?php echo $role->role_id;?></span></div>
						</div>
						<div class="control-group">
							<label for="" class="control-label">Name *</label>
							<div class="controls"><input type="text" name="name" required value="<?php echo $role->name;?>"></div>
						</div>
						<div class="control-group">
							<label for="" class="control-label">Type</label>
							<div class="controls">
								<span class="uneditable-input"><?php echo $role->role_type_id;?></span>
							</div>
						</div>
						<div class="control-group">
							<label for="" class="control-label">Enabled</label>
							<div class="controls"><input type="checkbox" name="enabled" <?php echo ($role->enabled==DB_TRUE ? 'checked=checked': '');?>></div>
						</div>
						<div class="control-group">
							<label for="" class="control-label">Authentication Service</label>
							<div class="controls">
								<span class="uneditable-input"><?php echo $role->authentication_service_id;?></span>
							</div>
						</div>
						<div class="control-group">
							<div class="controls"><button type="submit" class="btn btn-primary">Submit</button></div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="span3"></div>
	</div>

</div>

<?php $this->load->view('footer');?>