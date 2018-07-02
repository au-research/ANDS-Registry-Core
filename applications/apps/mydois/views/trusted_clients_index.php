<?php  $this->load->view('header');?>
<div class="content-header">
	<h1>List Trusted Clients</h1>
	<div class="btn-group">
		<a id="add_trusted_client_btn" href="javascript:;" class="btn btn-large"><i class="icon icon-plus"></i> Add Trusted Clients</a>
		<a id="fetch_unassigned_prefixes_btn" href="javascript:;" class="btn btn-large"><i class="icon icon-plus"></i> Fetch Unassigned Prefixes</a>
	</div>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
	<?php echo anchor('/mydois', 'Cite My Data'); ?>
	<?php echo anchor('mydois/list_trusted', 'List Trusted Clients', array('class'=>'current')); ?>
</div>
<div class="container-fluid" id="main-content">
	<div id='result_msg'>result</div>
	<div class="row-fluid">
		<!-- <div class="span2">&nbsp;</div> -->
		<div class="span12">
			<div id="trusted_clients">Loading...</div>

		</div>
		<!-- <div class="span3"></div> -->
	</div>
</div>

<div class="modal hide fade" id="add_trusted_client_modal">
	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Add Trusted Client</h3>
	</div>
	
	<div class="modal-screen-container">
		<div class="modal-body">
			<div id="add_client_msg" class="alert alert-info">
				Please provide the relevant information
			</div>
			<form action="#" method="get" class="form-horizontal" id="add_trusted_client_form">
				<div class="control-group">
					<label class="control-label">Client Name</label>
					<div class="controls">
						<input type="text" name="client_name" value=""/>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Contact Name</label>
					<div class="controls">
						<input type="text" name="client_contact_name" value=""/>
					</div>
				</div>	
				<div class="control-group">
					<label class="control-label">Contact Email</label>
					<div class="controls">
						<input type="text" name="client_contact_email" value=""/>
					</div>
				</div>	
				<div class="control-group">
					<label class="control-label">Domain(s)</label>
					<div class="controls">
						<input type="text" name="domainList" value=""/>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">DOI Prefix</label>
					<div class="controls">
						<select name="datacite_prefix" id="add_prefix_select">
						</select>
					</div>
				</div>																			
				<div class="control-group">
					<label class="control-label">IP Address Range(s)</label>
					<div class="controls">
						<input type="text" name="ip_address" value=""/>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label">Shared Secret</label>
					<div class="controls">
						<input type="text" name="shared_secret" value="<?php echo substr(md5(rand()), 0, 10); ?>"></input>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="modal-footer">
		<span id="result_msg"></span>
		<a id="add_confirm" href="javascript:;" class="btn btn-primary" data-loading-text="Adding...This might take several seconds">Add Trusted DOI Client</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
</div>
<div class="modal hide fade" id="edit_trusted_client_modal">
	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Update Trusted Client</h3>
	</div>
	
	<div class="modal-screen-container">
		<div class="modal-body">
			<div class="alert alert-info">
				Please provide the relevant information
			</div>
			<form action="#" method="get" class="form-horizontal" id="edit_trusted_client_form">
				<input type="hidden" name="client_id" value=""/>
				<div class="control-group">
					<label class="control-label">Client Name</label>
					<div class="controls">
						<input type="text" name="client_name" value=""/>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Contact Name</label>
					<div class="controls">
						<input type="text" name="client_contact_name" value=""/>
					</div>
				</div>	
				<div class="control-group">
					<label class="control-label">Contact Email</label>
					<div class="controls">
						<input type="text" name="client_contact_email" value=""/>
					</div>
				</div>	
				<div class="control-group">
					<label class="control-label">Domain(s)</label>
					<div class="controls">
						<input type="text" name="domainList" value=""/>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">DOI Prefix</label>
					<div class="controls">
						<select name="datacite_prefix" id="prefix_select">
						</select>
					</div>
				</div>																			
				<div class="control-group">
					<label class="control-label">IP Address Range(s)</label>
					<div class="controls">
						<input type="text" name="ip_address" value=""/>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">App Id</label>
					<div class="controls">
						<input type="text" name="app_id" value="" readonly/>
					</div>
				</div>				
				<div class="control-group">
					<label class="control-label">Shared Secret</label>
					<div class="controls">
						<input type="text" name="shared_secret" value=""/>
						<a href="javascript:;" class="sec_gen btn btn-small" tip="Generate shared secret" sec="<?php  echo substr(md5(rand()), 0, 10); ?>"client_id="{{client_id}}"><i class="icon icon-refresh"> </i></a>
							 Generate Shared Secret
					</div>
				</div>				
			</form>
		</div>
	</div>

	<div class="modal-footer">
		<span id="result_msg"></span>
		<a id="edit_confirm" href="javascript:;" class="btn btn-primary" data-loading-text="Updating...This might take several seconds">Update Trusted DOI Client</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
</div>

<script type="text/x-mustache" id="trusted_clients-template">
<div class="widget-box">
	<div class="widget-title">
		<h5>Trusted Clients</h5>
	</div>
	<div class="widget-content nopadding">
		<table class="table table-bordered data-table">
			<thead>
				<tr>
					<th>Client Symbol</th>
					<th>Client Name </th>
					<th>Contact Name </th>
					<th>App ID</th>
					<th>Active Prefix</th>
					<th>Other Prefixes</th>
					<th>Domain List</th>
					<th>Date Created</th>
					<th>Action </th>
				</tr>
			</thead>
			<tbody>
			{{#.}}
				<tr>
					<td><a href="{{url}}" target="_blank" title="view it in datacite">{{datacite_symbol}}</a></td>
					<td>{{client_name}}</td>
					<td>{{client_contact_name}}</td>
					<td>{{app_id}}</td>
					<td>{{datacite_prefix}}</td>
					<td>{{not_active_prefixes}}</td>
					<td>{{domain_list}}</td>
					<td>{{created_when}}</td>
					<td>
					<a href="javascript:;" class="edit btn btn-small" tip="Edit" app_id="{{app_id}}" client_id="{{client_id}}"><i class="icon-edit"></i></a> 
					<a href="javascript:;" class="remove btn btn-small btn-danger" tip="Remove" client_id="{{client_id}}"><i class="icon-white icon-remove"></i></a>
					</td>
				</tr>
			{{/.}}
			</tbody>
		</table>  
	</div>
</div>
</script>
<?php  $this->load->view('footer');?>