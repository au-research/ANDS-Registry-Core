<?php  $this->load->view('header');?>
<!--This is just a display of data that is being merged when the test and prod datacite accounts are merged as part of release 29  -->
<div class="content-header">
	<h1>Merge Trusted Clients</h1>

</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
	<?php echo anchor('/mydois', 'Cite My Data'); ?>
	<?php echo anchor('mydois/merge_trusted', 'Merge Trusted Clients', array('class'=>'current')); ?>
</div>
<div class="container-fluid" id="main-content">
	<div id='result_msg'>result</div>
	<div class="row-fluid">
		<!-- <div class="span2">&nbsp;</div> -->
		<div class="span12">
			<div id="merged_clients">Loading...</div>

		</div>
		<!-- <div class="span3"></div> -->
	</div>
</div>

<script type="text/x-mustache" id="merge_clients-template">
<div class="widget-box">
	<div class="widget-title">
		<h5>Trusted Clients</h5>
	</div>
	<div class="widget-content nopadding">
		<table class="table table-bordered data-table">
			<thead>
				<tr>

					<th>Client Name </th>
					<th>Test Client Name </th>
					<th>App ID</th>
					<th>Test App ID</th>
					<th>IP address</th>
					<th>Test IP address</th>
					<th>Combined IP Address</th>
					<th>Domain List</th>
					<th>Test Domain List</th>
					<th>Combined Domain List</th>
				</tr>
			</thead>
			<tbody>
			{{#.}}
				<tr>
					<td>{{client_name}}</td>
					<td>{{test_client_name}}</td>
					<td>{{app_id}}</td>
					<td>{{test_app_id}}</td>
					<td>{{ip_address}}</td>
					<td>{{test_ip_address}}</td>
					<td>{{combined_ip}}</td>
					<td>{{domain_list}}</td>
					<td>{{test_domain_list}}</td>
					<td>{{combined_domain_list}}</td>
				</tr>
			{{/.}}
			</tbody>
		</table>  
	</div>
</div>
</script>
<?php  $this->load->view('footer');?>