<?php  $this->load->view('header');?>
<div class="content-header">
	<h1>Updated Trusted Clients</h1>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
	<?php echo anchor('/mydois', 'Cite My Data'); ?>
	<?php echo anchor('mydois/update_all_password', 'List Trusted Clients', array('class'=>'current')); ?>
</div>
<div class="container-fluid" id="main-content">
	<div id='result_msg'>result</div>
	<div class="row-fluid">
		<!-- <div class="span2">&nbsp;</div> -->
		<div class="span12">
			<div id="updated_clients">Loading...</div>

		</div>
		<!-- <div class="span3"></div> -->
	</div>
</div>

<script type="text/x-mustache" id="update_clients-template">
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
                    <th>Shared_secret</th>
                    <th>Test Shared secret </th>
					<th>Date Created</th>
					<th>Action </th>
				</tr>
			</thead>
			<tbody>
			{{#.}}
			{{#display}}
				<tr>
					<td><a href="{{url}}" target="_blank" title="view it in datacite">{{datacite_symbol}}</a></td>
					<td>{{client_name}}</td>
					<td>{{shared_secret}}</td>
					<td>{{test_shared_secret}}</td>
					<td>{{created_when}}</td>
					<td>
					<a href="javascript:;" class="edit btn btn-small" tip="Edit" app_id="{{app_id}}" client_id="{{client_id}}"><i class="icon-edit"></i></a> 
					<a href="javascript:;" class="remove btn btn-small btn-danger" tip="Remove" client_id="{{client_id}}"><i class="icon-white icon-remove"></i></a>
					</td>
				</tr>
				{{/display}}
			{{/.}}
			</tbody>
		</table>  
	</div>
</div>
</script>
<?php  $this->load->view('footer');?>