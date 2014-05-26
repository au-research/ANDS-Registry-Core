<?php $this->load->view('header'); ?>
<div ng-app="ds_app">
	<div ng-view></div>
</div>

<div id="list_template" class="hide">
	<div class="content-header">
		<h1>Manage My Datasource</h1>
		<div class="btn-group">
			<a class="btn btn-large" href="#/new_page"><i class="icon icon-plus"></i> Add New Datasource</a>
		</div>
	</div>

	<div id="breadcrumb">
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'tip'=>'Go to Home'))?>
		<?php echo anchor('data_source/manage', 'Manage My Data Sources', array('class'=>'current'))?>
	</div>

	<div class="container-fluid" ng-show="stage=='loading'">
		Loading... Please wait
	</div>

	<div class="container-fluid" ng-show="stage=='complete'">
	
		<div class="row-fluid">
			<form action="" class="form-search">
				<div class="input-append">
					<input type="text" class="search-query" ng-model="filter" placeholder="Type to filter">
					<button class="btn">Filter</button>
				</div>
			</form>
		</div>
		

		<div class="widget-box" ng-repeat="ds in datasources | filter:filter">
			<div class="widget-title">
				<h5 class="ellipsis"><a title="{{ds.title}}" class="view" href="#!/view/{{ds.id}}">{{ds.title}}</a> ({{ds.record_owner}})</h5>
				<div class="btn-group item-control pull-right">
					<a href="#!/view/{{ds.id}}" class="btn btn-small"><i class="icon-eye-open"></i>Dashboard</a>
		  			<a href="<?=base_url('data_source/manage_records/');?>/{{ds.id}}" class="btn btn-small">Manage Records</a>
		  			<a href="#!/edit/{{ds.id}}" class="btn btn-small"><i class="icon-eye-open"></i>Edit Settings</a>
				</div>
			</div>
			
			<div class="widget-content">
				<span ng-repeat="count in ds.counts" class="tag goto status_{{count.status}}">{{count.name}} ({{count.count}})</span>
			</div>

		</div>
	</div>

</div>

<div id="view_template" class="hide">
	<div class="content-header">
		<h1>{{ds.title}}</h1>
		<ul class="nav nav-pills">
			<li class="active view page-control"><a href="#!/view/{{ds.id}}">Dashboard</a></li>
			<li class="mmr page-control"><a href="<?=base_url('data_source/manage_records/');?>/{{ds.id}}">Manage Records</a></li>
			<li class="report page-control"><a href="<?=base_url('data_source/report/');?>/{{ds.id}}">Reports</a></li>
			<li class="settings page-control"><a href="#!/settings/ds.id">Settings</a></li>
		</ul>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'tip'=>'Go to Home'))?>
		<a href="#!/">Manage My Data Sources</a>
		<a href="javascript:;" class="current">{{ds.title}} - Dashboard</a>
		<div class="pull-right">
			<span class="label"><i class="icon-question-sign icon-white"></i> <a target="_blank" style="color:white;" href="http://services.ands.org.au/documentation/DashboardHelp/">Help</a></span>
		</div>
	</div>
	<div class="container-fluid">
		<div class="row-fluid">

			<div class="span8">

				
	 			<div class="btn-toolbar">
					<div class="btn-group">
				  		<a href="#!/edit/{{ds.id}}" class="btn"><i class="icon-edit"></i> Edit Settings</a>
				  		<a href="<?=base_url('data_source/manage_records/');?>/{{ds.id}}" class="btn"><i class="icon-folder-open"></i> Manage Records</a>
				  		<a href="<?=base_url('data_source/manage_deleted_records/');?>/{{ds.id}}" class="btn"><i class="icon-time"></i> View Deleted Records</a>
						
					</div>
					<div class="btn-group pull-right">
						<a class="btn dropdown-toggle ExportDataSource" data-toggle="modal" href="#exportDataSource" id="exportDS">Export Records</a>						
					</div>
					<div class="btn-group pull-right">
						<a class="btn dropdown-toggle importRecords" data-toggle="dropdown" href="javascript:;">
							<i class="icon-download-alt"></i> Import Records <span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							<li><a data-toggle="modal" href="#importRecordsFromURLModal" id="importFromURLLink">From a URL</a></li>
							<li><a data-toggle="modal" href="#importRecordsFromXMLModal" id="importFromXMLLink">From pasted XML</a></li>
							<li><a href="" id="importFromHarvesterLink">From the Harvester</a></li>
						</ul>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title">
						<span class="icon"><i class="icon icon-refresh"></i></span>
						<h5 id="activity_log_title">Activity Log</h5>
						<div id="activity_log_switcher" class="pull-right">
							<select class="log-type btn-mini">
								<option value="all">All Logs</option>
								<option value="error">Errors</option>
							</select>
						</div>
					</div>
					<div class="widget-content nopadding">
						<ul class="activity-list" id="data_source_log_container">
							<li ng-repeat="log in ds.logs" class="{{log.type}}">
								<a href="" ng-click="log.show=!log.show" class="expand_log {{log.type}}">
									<i class="icon-list-alt"></i>{{log.log | truncate:105}}<span class="label">{{log.date_modified * 1000 | date:'medium'}}</span>
								</a>
								<div class="log" ng-show="log.show">
									<pre style="width:95%; float:left;">{{log.log}}</pre>
									<br class="clear"/>
								</div>
							</li>
						</ul>
						<ul class="activity-list">
							<li class="viewall">
								<a href="" class="tip-top" ng-click="more_logs()">Show More<i class='icon-arrow-down'></i> <span class="label label-info" d="log_summary"></span></a>
							</li>
						</ul>
					</div>
			    </div>

			</div>

			<div class="span4">

				<div class="widget-box">
					<div class="widget-title">
						<span class="icon" ng-click="refresh_harvest_status()"><i class="icon icon-refresh"></i></span>
						<h5>Harvester Status</h5>
					</div>
					<div class="widget-content">
						{{harvester.status}}
					</div>
					<div class="widget-content">
						<a href="" class="btn btn-primary">Import from Harvester</a>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title"><h5>Data Source Status Summary</h5></div>
					<div class="widget-content nopadding">
						<ul class="ro-list">
							<li ng-repeat="status in ds.counts" class="status_{{status.status}}"><span class="name">{{status.name}}</span><span class="num">{{status.count}}</span></li>
						</ul>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title"><h5>Data Source Class Summary</h5></div>
					<div class="widget-content nopadding">
						<ul class="ro-list">
							<li ng-repeat="status in ds.classcounts"><span class="name">{{status.name}}</span><span class="num">{{status.count}}</span></li>
						</ul>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title"><h5>Data Source Quality Summary</h5></div>
					<div class="widget-content nopadding">
						<ul class="ro-list">
							<li ng-repeat="status in ds.qlcounts"><span class="name">Quality Level {{status.level}}</span><span class="num">{{status.count}}</span></li>
						</ul>
					</div>
				</div>

				

				<?php if ($this->user->hasFunction('REGISTRY_SUPERUSER')): ?>
		  			<button class="btn btn-danger pull-right" data_source_title="{{title}}" data_source_id="{{data_source_id}}" id="delete_data_source_button"> <i class="icon-white icon-warning-sign"></i> Delete Data Source <i class="icon-white icon-trash"></i> </button>
				<?php endif; ?>

			</div>


		</div>
	</div>
</div>

<?php $this->load->view('footer'); ?>