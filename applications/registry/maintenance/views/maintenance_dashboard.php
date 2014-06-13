<?php $this->load->view('header'); ?>
<div ng-app="status_app">
	<div ng-view></div>
</div>

<div id="index_template" class="hide">
	<div class="content-header">
		<h1>Maintenance</h1>
	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
		<a href="#/" class="current">Maintenance</a>
	</div>
	
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span3">
				<div class="widget-box">
					<div class="widget-title">
						<h5>Index Status</h5>
					</div>
					<div class="widget-content">
						<dl class="dl">
							<dt>Solr URL</dt><dd>{{status.solr.url}}</dd>
						</dl>
					</div>
				</div>
			</div>
			<div class="span6">
				<div class="widget-box">
					<div class="widget-title">
						<h5>Configurations</h5>
					</div>
					<div class="widget-content">
						<form action="" class="form" ng-submit="save_config()">
							<fieldset>
								<legend>Harvester</legend>
								<div class="control-group">
									<label for="">Harvested Contents Path</label>
									<div class="controls">
										<input type="text" class="input-xxlarge" ng-model="config.harvested_contents_path.value">
									</div>
								</div>
							</fieldset>
							<button type="submit" class="btn btn-primary">Save</button>
						</form>
						<hr>
						<div class="alert alert-success" ng-show="message">{{message}}</div>
						<div class="alert alert-error" ng-show="error">{{error}}</div>
					</div>
				</div>
			</div>
			<div class="span3">

				<div class="widget-box">
					<div class="widget-title">
						<h5>Harvester Status</h5>
					</div>
					<div class="widget-content">
						<dl class="dl">
							<dt>Last Report</dt><dd>{{config.harvester_status.value.last_report_timestamp * 1000 | timeago}}</dd>
							<dt>Been Running For</dt><dd>{{config.harvester_status.value.start_up_time * 1000 | timeago}}</dd>
							<dt>Harvest Running</dt><dd>{{config.harvester_status.value.harvests_running}}</dd>
							<dt>Total Harvest Started</dt><dd>{{config.harvester_status.value.total_harvests_started}}</dd>
							<dt>Harvest Stopped</dt><dd>{{config.harvester_status.value.harvest_stopped}}</dd>
							<dt>Harvest Completed</dt><dd>{{config.harvester_status.value.harvest_completed}}</dd>				
							<dt>Harvest Cued</dt><dd>{{config.harvester_status.value.harvests_cued}}</dd>				
						</dl>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title">
						<h5>Admin</h5>
					</div>
					<div class="widget-content">
						<dl class="dl">
							<dt>Deployment State</dt><dd>{{status.deployment.state}}</dd>
							<dt>Admin</dt><dd>{{status.admin.name}}</dd>
							<dt>Admin Email</dt><dd>{{status.admin.email}}</dd>
						</dl>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('footer'); ?>