<?php 

/**
 * Viewing Role Interface
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<div class="content-header">
	<h1><?php echo $role->name;?></h1>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
	<?php echo anchor('/', 'List Roles'); ?>
	<?php echo anchor('/role/view/?role_id='.rawurlencode($role->role_id), $role->name, array('class'=>'current'));?>
</div>
<div class="container-fluid">
	<div class="row-fluid">

		<?php if(trim($role->role_type_id)!='ROLE_DOI_APPID'): ?>
		<div class="span8">
			<?php if(trim($role->role_type_id)=='ROLE_ORGANISATIONAL' || trim($role->role_type_id)=='ROLE_FUNCTIONAL'):?>
			<div class="widget-box">
				<div class="widget-title">
					<h5>Users</h5>
				</div>
				<div class="widget-content">
					<ul>
						<?php 
							foreach($users as $u){
								echo '<li>';
								echo anchor('/role/view/?role_id='.rawurlencode($u->role_id), $u->name);
								if($u->childs){
									echo '<ul>';
									foreach($u->childs as $uu){
										echo '<li>';
										echo anchor('/role/view/?role_id='.rawurlencode($uu->role_id), $uu->name);
										echo '</li>';
									}
									echo '</ul>';
								}
								//echo '<a href="javascript:;" class="remove_relation" tip="Remove This Role Relation" parent="'.$c->parent_role_id.'" child="'.$role->role_id.'"><i class="icon icon-remove"></i></a>';
								echo '</li>';
							}
						?>
					</ul>
					<form class="form-inline">
						<select class="chosen">
							<option value=""></option>
							<?php foreach($missingUsers as $u):?>
								<option value="<?php echo $u->role_id;?>"><?php echo $u->name;?></option>
							<?php endforeach;?>
						</select>
						<a href="javascript:;" child="<?php echo $role->role_id;?>"class="btn add_role add_role_reverse" tip="Add This Role Relation"><i class="icon icon-plus"></i> Add</a>
					</form>
				</div>
			</div>
			<?php endif;?>

			<?php if(trim($role->role_type_id)=='ROLE_ORGANISATIONAL' && $data_sources['status']=='OK'):?>
			<div class="widget-box">
				<div class="widget-title">
					<h5>Data sources</h5>
				</div>
				<div class="widget-content">
					<?php if($data_sources['numFound'] > 0):?>
						<ul>
						<?php 
							foreach($data_sources['result'] as $ds){
								echo '<li>';
								echo anchor($ds['registry_url'], $ds['title'], array('tip'=>$ds['key']));
								echo '</li>';
							}
						?>
						</ul>
					<?php else:?>
						<p>No data source is affiliate with this organisational role</p>
					<?php endif; ?>
				</div>
			</div>
			<?php endif;?>

			<div class="widget-box">
				<div class="widget-title"><h5>Functional Roles</h5></div>
				<div class="widget-content">
					<ul>
					<?php foreach($childs as $c):?>
						<?php
							if(trim($c->role_type_id) == "ROLE_FUNCTIONAL"){
								echo '<li>';
								echo anchor('/role/view/?role_id='.rawurlencode($c->parent_role_id), $c->name);
								echo '<a href="javascript:;" class="remove_relation" tip="Remove This Role Relation" parent="'.$c->parent_role_id.'" child="'.$role->role_id.'"><i class="icon icon-remove"></i></a>';
								if($c->childs){
									echo '<ul>';
									foreach($c->childs as $cc){
										echo '<li>';
										echo anchor('/role/view/?role_id='.rawurlencode($cc->parent_role_id), $cc->name);
										if($cc->childs){
											foreach($cc->childs as $ccc){
												echo '<ul>';
												echo anchor('/role/view/?role_id='.rawurlencode($ccc->parent_role_id), $ccc->name);
												echo '</ul>';
											}
										}
										echo '</li>';
									}
									echo '</ul>';
								}
								echo '</li>';
							}
						?>
					<?php endforeach;?>
					</ul>
					<form class="form-inline">
						<select class="chosen">
							<option value=""></option>
							<?php foreach($missingRoles['functional'] as $f):?>
								<option value="<?php echo $f->role_id;?>"><?php echo $f->name;?></option>
							<?php endforeach ?>
						</select>
						<a href="javascript:;" child="<?php echo $role->role_id;?>"class="btn add_role" tip="Add This Role Relation"><i class="icon icon-plus"></i> Add</a>
					</form>
				</div>
			</div>

			<div class="widget-box">
				<div class="widget-title"><h5>Organisational Roles</h5></div>
				<div class="widget-content">
					<ul>
					<?php foreach($childs as $c):?>
						<?php
							if(trim($c->role_type_id) == "ROLE_ORGANISATIONAL"){
								echo '<li>';
								echo anchor('/role/view/?role_id='.rawurlencode($c->parent_role_id), $c->name);
								echo '<a href="javascript:;" class="remove_relation" parent="'.$c->parent_role_id.'" child="'.$role->role_id.'" tip="Remove This Role Relation"><i class="icon icon-remove"></i></a>';
								echo '</li>';
							}
						?>
					<?php endforeach;?>
					</ul>
					<form class="form-inline">
						<select class="chosen">
							<option value=""></option>
							<?php foreach($missingRoles['organisational'] as $f):?>
								<option value="<?php echo $f->role_id;?>"><?php echo $f->name;?></option>
							<?php endforeach ?>
						</select>
						<a href="javascript:;" child="<?php echo $role->role_id;?>"class="btn add_role" tip="Add This Role Relation"><i class="icon icon-plus"></i> Add</a>
					</form>
				</div>
			</div>

			<?php if(trim($role->role_type_id)=='ROLE_ORGANISATIONAL' || trim($role->role_type_id)=='ROLE_USER'):?>
			<div class="widget-box">
				<div class="widget-title"><h5>DOI Application Identifier</h5></div>
				<div class="widget-content">
					<ul>
					<?php foreach($doi_app_id as $c):?>
						<?php
							if(trim($c->role_type_id) == "ROLE_DOI_APPID"){
								echo '<li>';
								echo anchor('/role/view/?role_id='.rawurlencode($c->parent_role_id), $c->name);
								echo '<a href="javascript:;" class="remove_relation" parent="'.$c->parent_role_id.'" child="'.$role->role_id.'" tip="Remove This Role Relation"><i class="icon icon-remove"></i></a>';
								echo '</li>';
							}
						?>
					<?php endforeach;?>
					</ul>
					<form class="form-inline">
						<select class="chosen">
							<option value=""></option>
							<?php foreach($missing_doi as $f):?>
								<option value="<?php echo $f->role_id;?>"><?php echo $f->name;?></option>
							<?php endforeach ?>
						</select>
						<a href="javascript:;" child="<?php echo $role->role_id;?>"class="btn add_role" tip="Add This Role Relation"><i class="icon icon-plus"></i> Add</a>
					</form>
				</div>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		
		<div class="<?php echo ((trim($role->role_type_id)=='ROLE_DOI_APPID') ? 'span8' : 'span4');?>">
			<div class="widget-box">
				<div class="widget-title">
					<h5><?php echo $role->name;?></h5>
				</div>
				<div class="widget-content">
					<table class="table table-bordered data-table">
						<tbody>
							<tr>
								<th>ID</th>
								<td><?php echo $role->role_id;?></td>
							</tr>
							<tr>
								<th>Name</th>
								<td><?php echo $role->name;?></td>
							</tr>
							<tr>
								<th>Type</th>
								<td><?php echo readable($role->role_type_id);?></td>
							</tr>
							<tr>
								<th>Authentication Service</th>
								<td><?php echo readable($role->authentication_service_id);?></td>
							</tr>
							<tr>
								<th>Enabled</th>
								<td><?php echo readable($role->enabled);?></td>
							</tr>
							<tr>
								<th>Last Login</th>
								<td><?php echo $role->last_login;?></td>
							</tr>
							<tr>
								<th>Created When</th>
								<td><?php echo $role->created_when;?></td>
							</tr>
							<tr>
								<th>Created Who</th>
								<td><?php echo $role->created_who;?></td>
							</tr>
							<tr>
								<th>Modified When</th>
								<td><?php echo $role->modified_when;?></td>
							</tr>
							<tr>
								<th>Modified Who</th>
								<td><?php echo $role->modified_who;?></td>
							</tr>
						</tbody>
					</table>  
					<?php echo anchor('role/edit/'.rawurlencode($role->role_id), 'Edit', array('class'=>'btn btn-primary'));?>
					<a class="btn btn-danger" id="delete_role" role_id="<?php echo $role->role_id?>">Delete</a>

					<?php if($role->authentication_service_id == 'AUTHENTICATION_BUILT_IN') 
						echo '<button class="btn btn-danger" id="reset_pw" role_id="'.$role->role_id.'">Reset Password</button>'
					?>
					<span id="msg"></span>
				</div>
			</div>
			
		</div>

	</div>

</div>

<?php $this->load->view('footer');?>