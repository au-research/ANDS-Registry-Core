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
									<th>ID</th>
									<th ng-click="predicate = 'title';reverse=!reverse">Title</th>
									<th ng-click="predicate = 'total_published';reverse=!reverse">Total Published</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<tr ng-repeat="ds in datasources | orderBy:predicate:reverse | filter: filters.title " ng-show="ds.total_published > 0">
									<td>{{ds.id}}</td>
									<td>{{ds.title}}</td>
									<td>{{ds.total_published}}</td>
									<td>
										<div class="btn-group">
											<button class="btn btn-default" ng-click="addTask('sync', ds.id)">Sync</button>
											<!-- <button class="btn dropdown-toggle" data-toggle="dropdown">
												<span class="caret"></span>
											</button>
											<ul class="dropdown-menu">
												<li><a href="">Enrich</a></li>
												<li><a href="">Index</a></li>
											</ul> -->
										</div>
									</td>
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
							Task <b>{{ct.task}}</b> on Data Source: <b>{{ct.ds_id}}</b> Finished. for a total of <b>{{ct.total}}</b> Registry Objects in <b>{{ct.totalTime | number:2}}</b> seconds.
						</div>
						<div class="alert alert-info" ng-show="ct.status=='running'">
							<dl class="dl-horizontal">
								<dt>Task</dt> <dd>{{ct.task}}</dd>
								<dt>Data Source ID</dt> <dd>{{ct.ds_id}}</dd>
								<dt>Status</dt> <dd>{{ct.status}}</dd>
								<dt>Total</dt> <dd>{{ct.total}}</dd>
								<dt>Chunking:</dt> <dd>{{currentChunk}}/{{ct.numChunk}}</dd>
								<dt>Elapsed Time:</dt> <dd>{{totalTime}}</dd>
							</dl>
						</div>
						<progress percent="percent" class="progress-striped active"></progress>
					</div>
				</div>

				<div class="widget-box" ng-show="errors">
					<div class="widget-title">
						<h5>Error</h5>
					</div>
					<div class="widget-content" ng-repeat="e in errors">
						<p>{{e.key}}</p>
						<div class="alert alert-error" ng-repeat="msg in e.error_msg">
							{{msg}}
						</div>
						<hr>
					</div>
				</div>
			</div>
			
			
		</div>

		
	</div>
</div>

<?php $this->load->view('footer'); ?>