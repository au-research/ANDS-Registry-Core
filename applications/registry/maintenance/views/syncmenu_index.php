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
		<?php echo anchor(registry_url('maintenance'), 'Maintenance'); ?>
		<a href="#/" class="current">Sync Menu</a>
	</div>
	
	<div class="container-fluid">

		<div class="row-fluid" ng-show="loading_detailed_stat">
			<div class="span12 center alert alert-info">Loading Detailed Data Sources Stat. Please wait...</div>
		</div>

		<div class="row-fluid">
			<div class="span12 center alert alert-info" ng-show="loading_global_stat">Loading Global Stat. Please wait...</div>
			<div class="span12 center" style="text-align: center;" ng-show="global_stat">
				<ul class="stat-boxes">
					<li>
						<div class="left peity_bar_good"><span>Database</span>Registry Objects</div>
						<div class="right">
							<strong>{{global_stat.totalCountDB}}</strong>
						</div>
					</li>
					<li>
						<div class="left peity_bar_good"><span>Database</span>Published</div>
						<div class="right">
							<strong>{{global_stat.totalCountDBPublished}}</strong>
						</div>
					</li>
					<li>
						<div class="left peity_bar_neutral"><span>SOLR Indexed</span>Registry Objects</div>
						<div class="right">
							<strong>{{global_stat.totalCountSOLR}}</strong>
						</div>
					</li>
					<li>
						<div class="left peity_bar_bad"><span>Missing</span>Registry Objects</div>
						<div class="right">
							<strong>{{global_stat.notIndexed}}</strong>
						</div>
					</li>
				</ul>
			</div>
		</div>

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
									<th ng-click="predicate = 'id';reverse=!reverse">ID</th>
									<th ng-click="predicate = 'title';reverse=!reverse">Title</th>
									<th ng-click="predicate = 'total_published';reverse=!reverse">Total Published</th>
									<th ng-click="predicate = 'total_indexed';reverse=!reverse" ng-show="detailed_stat">Total Indexed</th>
									<th ng-click="predicate = 'total_missing';reverse=!reverse" ng-show="detailed_stat">Total Missing</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<tr ng-repeat="ds in datasources | orderBy:predicate:reverse | filter: filters.title " ng-show="ds.total_published > 0">
									<td>{{ds.id}}</td>
									<td>{{ds.title}}</td>
									<td>{{ds.total_published}}</td>
									<td ng-show="detailed_stat">{{ds.total_indexed}}</td>
									<td ng-show="detailed_stat">{{ds.total_missing}}</td>
									<td>
										<div class="btn-group">
											<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">Queue <span class="caret"></span></button>
											<ul class="dropdown-menu">
												<li><a href="" ng-click="addTask('sync', ds.id)">Sync</a></li>
												<li class="divider"></li>
												<li><a href="" ng-click="addBGSync(ds.id)">Background Sync</a></li>
												<li class="divider"></li>
												<li><a href="" ng-click="addTask('index', ds.id)">Index</a></li>
												<li><a href="" ng-click="addTask('clear', ds.id)">Clear Index</a></li>
											</ul>
										</div>
										<div class="btn-group">
											
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
					<div class="widget-content">
						<form class="form-search pull-left" ng-submit="syncRO()">
							<div class="input-prepend">
								<button type="submit" class="btn">Sync</button>
								<input type="text" class="input-medium search-query" placeholder="Key, ID or Slug" ng-model="subject">
							</div>
							<span ng-bind-html="syncROStatus"></span>
						</form>
						<div class="clear"></div>
					</div>
					<div class="widget-content">
						<button class="btn btn-default" ng-click="get_global_stat()" ng-show="!loading_global_stat">Load Global Stats</button>
						<button class="btn btn-default" ng-click="get_detailed_stat()" ng-show="!loading_detailed_stat">Load Detailed Stats</button>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title">
						<span class="icon" ng-click="refreshTask()"><i class="icon icon-refresh"></i></span>
						<h5>Background Tasks Queue</h5>
					</div>
					<div class="widget-content" ng-if="tasks">
						<dl class="dl-horizontal">
							<dt>Running Task</dt> <dd>{{tasks.running.length}}</dd>
							<dt>Pending Tasks</dt> <dd>{{tasks.pending.length}}</dd>
							<dt>Completed Tasks</dt> <dd>{{tasks.completed}}</dd>
						</dl>
						<hr>
						<select ng-model="pendingTaskShow" name="" id="">
							<option value="5">5</option>
							<option value="10">10</option>
							<option value="50">50</option>
							<option value="100">100</option>
						</select>
						<ul>
							<li ng-repeat="task in tasks.running" tip="{{task.params}}">{{task.name}} <span class="label label-info">RUNNING</span></li>
							<li ng-repeat="task in tasks.pending | limitTo:pendingTaskShow" tip="{{task.params}}">{{task.name}} <span class="label label-default">PENDING</span></li>
						</ul>
					</div>
					<div class="widget-content">
						<button class="btn btn-default" ng-click="clearPendingBGTasks()">Clear Pending Tasks</button>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title">
						<h5>Queue</h5>
					</div>
					<div class="widget-content">
						<div class="btn-group">
							<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">Mass Queue <span class="caret"></span></button>
							<ul class="dropdown-menu">
								<li class="disabled"><a href="">Small Datasources (&lt; 400)</a></li>
								<li><a href="" ng-click="massAddTask('sync', 'small')">Sync</a></li>
								<li><a href="" ng-click="massAddBGSync('small')">Background Sync</a></li>
								<li><a href="" ng-click="massAddTask('index', 'small')">Index</a></li>
								<li><a href="" ng-click="massAddTask('clear', 'small')">Clear Index</a></li>
								<li class="divider"></li>
								<li class="disabled"><a href="">Medium Datasources (400 - 1000)</a></li>
								<li><a href="" ng-click="massAddTask('sync', 'medium')">Sync</a></li>
								<li><a href="" ng-click="massAddBGSync('medium')">Background Sync</a></li>
								<li><a href="" ng-click="massAddTask('index', 'medium')">Index</a></li>
								<li><a href="" ng-click="massAddTask('clear', 'medium')">Clear Index</a></li>
								<li class="divider"></li>
								<li class="disabled"><a href="">Big Datasources (&gt; 1000)</a></li>
								<li><a href="" ng-click="massAddTask('sync', 'big')">Sync</a></li>
								<li><a href="" ng-click="massAddBGSync('large')">Background Sync</a></li>
								<li><a href="" ng-click="massAddTask('index', 'big')">Index</a></li>
								<li><a href="" ng-click="massAddTask('clear', 'big')">Clear Index</a></li>
							</ul>
							<button class="btn btn-default" ng-click="queue=[]">Clear Queue</button>
						</div>

					</div>
					<div class="widget-content">
						<ol>
							<li ng-repeat=" t in queue">{{t.task}} : Data Source: {{t.ds_id}}
								<span ng-show="t.status=='pending'" class="label">Waiting</span>
								<span ng-show="t.status=='done'" class="label label-success">Finished</span>
								<span ng-show="t.status=='running'" class="label label-info">Running</span>
							</li>
						</ol>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title"><h5>Solr Selective Syncing</h5></div>
					<div class="widget-content">
						<input type="text" ng-model="solr_query" placeholder="Solr Query">
						<span ng-show="solr_result">{{solr_result.response.numFound}} records found</span>
						<hr>
						<button class="btn btn-default" ng-click="solr_search()">Search</button>
						<button class="btn btn-default" ng-show="solr_result && solr_result.response.numFound > 0" ng-click="solr_query_sync()">BG Sync {{solr_result.response.numFound}}</button>
					</div>
				</div>

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
								<dt>{{percent | number:2}} %</dt> <dd></dd>
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

<div id="view_ds_template" class="hide">
	<div ng-show="ds">
		<div class="content-header">
			<h1>{{ds.ds.attributes.title.value}}</h1>
		</div>
		<div id="breadcrumb" style="clear:both;">
			<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
			<a href="#/">Sync Menu</a>
			<a href="#/view/">{{ds.ds.attributes.title.value}}</a>
		</div>
	</div>
</div>

<?php $this->load->view('footer'); ?>