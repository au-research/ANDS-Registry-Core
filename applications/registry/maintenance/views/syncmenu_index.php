<?php $this->load->view('header'); ?>
<div ng-app="sync_app">
	<div ng-view></div>
</div>

<div id="index_template" class="hide">
	<div class="content-header">
		<h1>Sync Menu</h1>
	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
		<a href="#/" class="current">Sync Menu</a>
	</div>
	
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span8">
				<div class="widget-box">
					<div class="widget-title">
						<h5>Data Sources</h5>
						<input type="text" style="float:right;margin:3px 10px;" placeholder="Filter" ng-model="filters.title">
					</div>
					<div class="widget-content nopadding">
						<table class="table table-bordered data-table">
							<thead>
								<tr>
									<th ng-click="predicate = 'title';reverse=!reverse">Title</th>
									<th ng-click="predicate = 'total_published';reverse=!reverse">Total Published</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<tr ng-repeat="ds in datasources | orderBy:predicate:reverse | filter: filters.title " ng-show="ds.total_published > 0">
									<td>{{ds.title}}</td>
									<td>{{ds.total_published}}</td>
									<td><button class="btn btn-default" ng-click="addTask('sync', ds.id)">Sync</button></td>
								</tr>							
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<div class="span4">
				<div class="widget-box">
					<div class="widget-title">
						<h5>Current Operation: {{ct.status}}</h5>
					</div>
					<div class="widget-content">
						<div class="alert alert-info" ng-show="ct.status=='idle'">There are no process running</div>
						<div class="alert alert-success" ng-show="ct.status=='done'">
							Task <b>{{ct.task}}</b> on Data Source: <b>{{ct.ds_id}}</b> Finished. for a total of <b>{{ct.total}}</b> Registry Objects in <b>{{ct.totalTime}}</b> seconds.
						</div>
						<div class="alert alert-info" ng-show="ct.status=='running'">
							<dl class="dl-horizontal">
								<dt>Task</dt>
								<dd>{{ct.task}}</dd>
								<dt>Data Source ID</dt>
								<dd>{{ct.ds_id}}</dd>
								<dt>Status</dt>
								<dd>{{ct.status}}</dd>
								<dt>Total</dt>
								<dd>{{ct.total}}</dd>
								<dt>Chunking:</dt>
								<dd>{{currentChunk}}/{{ct.numChunk}}</dd>
							</dl>
						</div>
						<progress percent="percent" class="progress-striped active"></progress>
					</div>
				</div>
			</div>
			
			
		</div>

		
	</div>
</div>

<?php $this->load->view('footer'); ?>