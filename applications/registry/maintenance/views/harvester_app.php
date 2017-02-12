<?php $this->load->view('header'); ?>
<div ng-app="harvester_app">
	<div ng-view></div>
</div>

<div id="index_template" class="hide">
	<div class="content-header">
		<h1>Harvester Management</h1>
	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
		<?php echo anchor(registry_url('maintenance'), 'Maintenance'); ?>
		<a href="#/" class="current">Harvester Management</a>
	</div>
	
	<div class="container-fluid">

		<div class="row-fluid">
			<div class="span12">
				<div class="widget-box">
					<div class="widget-title">
						<span class="icon" ng-click="refresh(true)"><i class="icon icon-refresh"></i></span>
						<h5>Harvests</h5>
						<input type="text" style="float:right;margin:3px 10px;" placeholder="Filter" ng-model="filters.title">
					</div>
					<div class="widget-content nopadding">
						<table class="table table-bordered data-table">
							<thead>
								<tr>
									<th>Actions</th>
									<th ng-click="predicate = 'data_source_title';reverse=!reverse">Data Source</th>
									<th ng-click="predicate = 'status';reverse=!reverse">Status</th>
									<th>Progress</th>
									<th ng-click="predicate = 'record_owner';reverse=!reverse">Record Owner</th>
									<th ng-click="predicate = 'next_run';reverse=!reverse">Next Run</th>
									<th ng-click="predicate = 'last_run';reverse=!reverse">Last Run</th>
									<th ng-click="predicate = 'harvest_id';reverse=!reverse">Harvest ID</th>
								</tr>
							</thead>
							<tbody>
								<tr ng-repeat="r in requests | orderBy:predicate:reverse | filter: filters.title ">
									<td>
										<div class="btn-group">
											<button class="btn btn-primary btn-small" ng-class="{false:'disabled', true:''}[r.can_start]" ng-click="start_harvest(r)"><i class="icon icon-white icon-play"></i></button>
											<button class="btn btn-danger btn-small" ng-class="{false:'disabled', true:''}[r.can_stop]" ng-click="stop_harvest(r)"><i class="icon icon-white icon-stop"></i></button>
										</div>
									</td>
									<td><a href="<?php echo base_url('data_source')?>#!/view/{{r.data_source_id}}">{{r.data_source_title}}</a></td>
									<td>{{r.status}}</td>
									<td>
										<span ng-show="r.status=='HARVESTING'">
											<span ng-show="r.message.progress.total=='unknown'">Current: {{r.message.progress.current}} ({{r.message.progress.time}} seconds)</span>
											<span ng-show="r.message.progress.total!='unknown' && r.message.progress.total">{{r.message.progress.current}}/{{r.message.progress.total}} ({{r.message.progress.time}} seconds)</span>
										</span>
										<div ng-show="r.message.error.log" class="alert alert-error">{{r.message.error.log}}</div>
									</td>
									<td>{{r.record_owner}}</td>
									<td>{{r.next_run}}</td>
									<td>{{r.last_run}}</td>
									<td>{{r.harvest_id}}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>

		</div>

		
	</div>
</div>

<?php $this->load->view('footer'); ?>