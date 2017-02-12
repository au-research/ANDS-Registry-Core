<?php 

/**
 * Adding Role Interface
 *  
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * 
 */
?>

<?php  $this->load->view('header');?>
<div class="content-header">
	<h1>Add Role</h1>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
	<?php echo anchor('/', 'List Roles'); ?>
	<?php echo anchor('/role/add/', 'Add Role',array('class'=>'current'));?>
</div>
<div class="container-fluid">
	<div class="row-fluid">

		<div class="span3"></div>
		<div class="span6">
			<div class="widget-box">
				<div class="widget-title"><h5>Add Role</h5></div>
				<div class="widget-content">
					<form action="?posted=true" method="post" class="form-horizontal" id="add_form">
						<?php if(isset($message)): ?>
							<div class="alert alert-danger"><?php echo $message ?></div>
						<?php endif; ?>
						<div class="control-group">
							<label for="" class="control-label">ID *</label>
							<div class="controls"><input type="text" name="role_id" required id="role_id"></div>
						</div>
						<div class="control-group">
							<label for="" class="control-label">Name *</label>
							<div class="controls"><input type="text" name="name" required></div>
						</div>
						<div class="control-group">
							<label for="" class="control-label">Type</label>
							<div class="controls">
								<select name="role_type_id" id="role_type_id">
									<option value="ROLE_USER">User</option>
									<option value="ROLE_ORGANISATIONAL">Organisational</option>
									<option value="ROLE_FUNCTIONAL">Functional</option>
									<option value="ROLE_DOI_APPID">DOI Application Identifier</option>
								</select>
							</div>
						</div>
						<div class="control-group">
							<label for="" class="control-label">Enabled</label>
							<div class="controls"><input type="checkbox" name="enabled" checked="checked"></div>
						</div>
						<div class="control-group" id="authentication_id">
							<label for="" class="control-label">Authentication Service</label>
							<div class="controls">
								<select name="authentication_service_id">
									<option value="AUTHENTICATION_BUILT_IN">Built In</option>
									<option value="AUTHENTICATION_LDAP">LDAP</option>
									<option value="AUTHENTICATION_SHIBBOLETH">Shibboleth</option>
								</select>
							</div>
						</div>
						<div class="alert alert-danger hide" id="msg"></div>
						<div class="control-group">
							<div class="controls"><button type="submit" class="btn btn-primary">Add Role</button></div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="span3"></div>
	</div>

</div>

<?php $this->load->view('footer');?>