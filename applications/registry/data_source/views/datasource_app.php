<?php $this->load->view('header'); ?>
<div ng-app="ds_app">
	<div ng-view></div>
</div>

<div id="list_template" class="hide">
	<div class="content-header">
		<h1>Manage My Datasource</h1>
		<?php if($this->user->hasFunction('REGISTRY_SUPERUSER')): ?>
		<div class="btn-group">
			<a class="btn btn-large" data-toggle="modal" href="#new_ds"><i class="icon icon-plus"></i> Add New Datasource</a>
		</div>
		<?php endif; ?>
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

	<div class="modal hide" id="new_ds">
		<div class="modal-header">
			<a href="javascript:;" class="close" data-dismiss="modal">×</a>
			<h3>Add New Data Source</h3>
		</div>
		
		<div class="modal-screen-container">
			<div class="modal-body">
				<div class="alert alert-info">
					Please provide the key and the title for the data source
				</div>
				<form action="#" method="get" class="form-vertical">
					<div class="control-group">
						<label class="control-label">Key</label>
						<div class="controls">
							<input type="text" name="data_source_key" required>
							<span class="help-block">Key has to be unique</span>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" name="title">Title</label>
						<div class="controls"><input required type="text" name="title"></div>
					</div>
					<div class="control-group">
						<label class="control-label" name="title">Owner</label>
						<div class="controls">
							<select name="record_owner">
								<?php foreach($this->user->affiliations() as $a):?>
								<option value="<?php echo $a;?>"><?php echo $a;?></option>
								<?php endforeach;?>
							</select>
						</div>
					</div>
				</form>
				<div class="alert alert-error" ng-show="error">
					{{error}}
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<a href="javascript:;" class="btn btn-primary" ng-click="add_new()">Save</a>
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
			<li class="settings page-control"><a href="#!/settings/{{ds.id}}">Settings</a></li>
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

						<a class="btn dropdown-toggle" ng-click="open_export_modal()"><i class="icon icon-hdd"></i> Export Records</a>						
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title">
						<span class="icon" ng-click="refresh_harvest_status()"><i class="icon icon-refresh"></i></span>
						<h5>Harvester Status</h5>
					</div>
					<div class="widget-content">
						<dl class="dl dl-horizontal">
							<dt>Status</dt><dd><span class="label label-info">{{harvester.status}}</span></dd>
							<dt>URI</dt><dd>{{ds.uri}}</dd>
							<span ng-show="harvester.last_run"><dt>Last Run</dt><dd>{{harvester.last_run}} ({{harvester.last_run | timeago}})</dd></span>
							<span ng-show="harvester.next_run">
								<dt ng-show="harvester.status!='COMPLETED' && harvester.status!='SCHEDULED'">Current Run</dt>
								<dt ng-show="harvester.status=='COMPLETED' || harvester.status=='SCHEDULED'">Next Run</dt>
								<dd>{{harvester.next_run}} ({{harvester.next_run | timeago}})</dd>
							</span>
							<span ng-show="ds.harvest_frequency"><dt>Harvest Frequency</dt><dd>{{ds.harvest_frequency}} starting from {{ds.harvest_date}} (AEST)</dd></span>
							<span ng-show="!ds.harvest_frequency"><dt>Harvest Frequency</dt><dd>Once Off</dd></span>
							<span ng-show="harvester.percent">
								<dt>Percent Complete</dt>
								<dd>{{harvester.percent}} %</dd>
							</span>
							<span ng-show="!harvester.percent && harvester.status=='HARVESTING'">
								<dt>Records Received</dt>
								<dd>{{harvester.message.progress.current}}</dd>
							</span>
						</dl>
						<div ng-show="!harvester.percent && harvester.status=='HARVESTING'">
							<div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div></div>
						</div>
						<div class="progress progress-striped active" ng-show="harvester.percent">
							<div class="bar" style="width: {{harvester.percent}}%;"></div>
						</div>
						<div class="alert alert-warning" ng-show="ds.harvest_method=='PMHHarvester' && harvester.status=='HARVESTING'">
							Progress indication for OAI-PMH harvest is an estimate only
						</div>
						<div ng-show="harvester.message.message" class="alert alert-info">
							{{harvester.message.message}}
						</div>
						<div ng-show="harvester.message.error.log" class="alert alert-error">
							{{harvester.message.error.log}}
						</div>
					</div>
					<div class="widget-content">
						<div class="btn-group pull-right">
							<a href="" class="btn btn-primary disabled" ng-show="harvester.status=='IMPORTING'">Importing...</a>
							<a href="" class="btn btn-primary" ng-click="start_harvest()" ng-show="harvester.can_start"><i class="icon icon-white icon-download-alt"></i> Import from Harvester</a>
							<a href="" class="btn btn-danger" ng-click="stop_harvest()" ng-show="harvester.can_stop && harvester.status!='SCHEDULED'"><i class="icon icon-white icon-stop"></i> Stop Harvest</a>
							<a href="" class="btn btn-danger" ng-click="stop_harvest()" ng-show="harvester.can_stop && harvester.status=='SCHEDULED'">Cancel Scheduled Harvest</a>
							<a href="" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
							<ul class="dropdown-menu pull-right">
								<li><a href="" ng-click="open_import_modal('url')"><i class="icon icon-globe"></i> Import from URL</a></li>
								<li><a href="" ng-click="open_import_modal('xml')"><i class="icon icon-briefcase"></i> Import from Pasted XML</a></li>
								<!--li><a href="" ng-click="open_import_modal('upload')"><i class="icon icon-file"></i> Import from File</a></li-->
								<!--li><a href="" ng-click="open_import_modal('path')"><i class="icon icon-download"></i> Import from Harvested Path</a></li-->
							</ul>
						</div>
						<div class="clearfix"></div>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title">
						<span class="icon" ng-click="get_latest_log(true)"><i class="icon icon-refresh"></i></span>
						<h5 id="activity_log_title">Activity Log <small class="muted" ng-show="ds.refreshing">Refreshing</small></h5>
						<div id="activity_log_switcher" class="pull-right">
							<select class="btn-mini" ng-model="log_type">
								<option value="">All Logs</option>
								<option value="error">Errors</option>
							</select>
						</div>
					</div>
					<div class="widget-content nopadding">
						<ul class="activity-list" id="data_source_log_container">
							<li ng-repeat="log in ds.logs | filter:log_type" class="{{log.type}}">
								<a href="" ng-click="log.show=!log.show" class="expand_log {{log.type}}">
									<i class="icon-list-alt"></i>{{log.header | truncate:105}}<span class="label">{{log.date_modified * 1000 | timeago}}</span>
								</a>
								<div class="log" ng-show="log.show">
									<img ng-show="log.harvester_error_type" ng-click="show_error(log)" src="<?=asset_url('img/Question-mark-icon.png','base');?>" alt="" style="float:right;cursor:pointer;">
									<pre style="width:95%; float:left;">{{log.log}}</pre>
									<br class="clear"/>
								</div>
							</li>
						</ul>
						<ul class="activity-list" ng-show="!nomore">
							<li class="viewall">
								<a href="" class="tip-top" ng-click="more_logs()">Show More<i class='icon-arrow-down'></i> <span class="label label-info" d="log_summary"></span></a>
							</li>
						</ul>
					</div>
			    </div>
			</div>

			<div class="span4">

				<div class="widget-box">
					<div class="widget-title"><h5>Data Source Status Summary</h5></div>
					<div class="widget-content nopadding">
						<ul class="ro-list">
							<li ng-repeat="status in ds.counts" class="status_{{status.status}}" ng-click="mmr_link('status', status.status)"><span class="name">{{status.name}}</span><span class="num">{{status.count}}</span></li>
						</ul>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title"><h5>Data Source Class Summary</h5></div>
					<div class="widget-content nopadding">
						<ul class="ro-list">
							<li ng-repeat="status in ds.classcounts" ng-click="mmr_link('class', status.class)"><span class="name"><i class="icon-class icon-{{status.class}}" style="margin-top:-1px;"></i> {{status.name}}</span><span class="num">{{status.count}}</span></li>
						</ul>
					</div>
				</div>

				<div class="widget-box">
					<div class="widget-title"><h5>Data Source Quality Summary</h5></div>
					<div class="widget-content nopadding">
						<ul class="ro-list">
							<li ng-repeat="status in ds.qlcounts" ng-click="mmr_link('quality_level', status.level)"><span class="name">Quality Level {{status.level}}</span><span class="num">{{status.count}}</span></li>
						</ul>
					</div>
				</div>

				<?php if ($this->user->hasFunction('REGISTRY_SUPERUSER')): ?>
				<div class="widget-box">
					<div class="widget-title"><h5>Data Source Downloaded Path</h5></div>
					<div class="widget-content">
						<ul>
							<li ng-repeat="f in files">
								<a href="" ng-click="toggle(f)"><i class="icon" ng-class="{'file':'icon-file','folder':'icon-folder-open'}[f.type]"></i> {{f.name}}</a>
								<ul ng-show="f.type=='folder' && f.show">
									<li ng-repeat="fi in f.files"><a href="" ng-click="toggle(fi)"><i class="icon icon-file"></i> {{fi.name}}</a></li>
								</ul>
							</li>
						</ul>
					</div>
				</div>
				<?php endif; ?>

				<?php if ($this->user->hasFunction('REGISTRY_SUPERUSER')): ?>
				<div class="widget-box">
					<div class="widget-content">
						<button class="btn btn-danger" ng-click="clear_logs()"><i class="icon-white icon-remove"></i> Clear Logs</button>
						<button class="btn btn-danger" ng-click="remove()"> <i class="icon-white icon-warning-sign"></i> Delete Data Source <i class="icon-white icon-trash"></i> </button>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="modal hide" id="file_content">
		<div class="modal-header">
			<a href="javascript:;" class="close" data-dismiss="modal">×</a>
			<h3>File Content</h3>
			<pre class="prettyprint linenums"><code class="language-xml" ng-bind="file_content"></code></pre>
		</div>
	</div>

	<div class="modal hide" id="import_modal">
		<div class="modal-header">
			<a href="javascript:;" class="close" data-dismiss="modal">×</a>
			<h3>{{importer.title}}</h3>
		</div>
		<div class="modal-screen-container">
			<div class="modal-body" ng-show="importer.type=='url'">
				<div class="alert alert-info">Import registry objects from a test feed or RIFCS XML file.</div>
				<form class="form-horizontal">
					<label class="control-label">URL to import records from:</label>
					<div class="controls">
						<input type="text" name="url" placeholder="http://" id="importer_url"/>
						<p class="help-block">
							<small>Use full URL format (including http://)</small>
						</p>
					</div>
				</form>
				<span class="label label-info">Note</span>
				<small>
					This tool will import RIFCS from an XML file using an Advanced Harvest Mode of Standard. For all other operations (such as OAI-PMH), please configure a Harvest from the Data Source Settings page. 
				</small>
			</div>
			<div class="modal-body" ng-show="importer.type=='xml'">
				<div class="alert alert-info">Paste the registry object contents into the field below</div>
				<form class="form-vertical">
					<fieldset>
						<label> <b>Data to import:</b>
						</label>
						<textarea name="xml" id="importer_xml" rows="18" style="width:97%;font-family:Courier;font-size:8px;line-height:9px;"></textarea>
					</fieldset>
				</form>
				<span class="label label-info">Note</span>
				<small>
					This tool will import pasted RIFCS contents using an Advanced Harvest Mode of Standard. For all other operations (such as OAI-PMH), please configure a Harvest from the Data Source Settings page. 
				</small>
				<small>
					This tool is designed for small imports (&lt;100 records). It may fail with larger bulk imports.
				</small>
			</div>
			<div class="modal-body" ng-show="importer.type=='upload'">
				<div class="alert alert-info">Import from Uploading File. Make sure uploaded file is XML and is valid</div>
				<form action="" class="form">
					<fieldset>
						<label for="">File to upload</label>
						<div class="controls">
							<input type="file">
						</div>
					</fieldset>
				</form>
			</div>
			<div class="modal-body" ng-show="importer.type=='path'">
				<div class="alert alert-info">Import from Previous Harvest</div>
				<form action="" class="form-horizontal">
					<fieldset>
						<label for="">Previous Batch:</label>
						<input type="text" class="input-xlarge uneditable-input" ng-model="harvester.message.batch_number" id="importer_batch"/>
					</fieldset>
				</form>
			</div>
			<div class="modal-body" ng-show="importer.result.message">
				<div class="alert alert-{{importer.result.type}}" style="white-space: pre; word-wrap:break-word">{{importer.result.message}}</div>
			</div>
			<div class="modal-body" ng-show="importer.running">
				<div class="progress progress-striped active">
					<div class="bar" style="width: 100%;"></div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<a href="" ng-click="import()" class="btn btn-primary" ng-class="{true:'disabled', false:''}[importer.running]">
				<span ng-show="!importer.running">Import Records</span>
				<span ng-show="importer.running">Importing... Please wait</span>
			</a>
			<a href="javascript:;" data-dismiss="modal" class="btn" ng-show="importer.result.message">Ok</a>
		</div>
	</div>

	<div class="modal hide fade" id="exportDataSource">
		<div class="modal-header">
			<a href="javascript:;" class="close" data-dismiss="modal">×</a>
			<h3>Export Records As RIF-CS</h3>
		</div>
		
		<div class="modal-screen-container">
			<div name="selectionScreen" class="modal-body">
				
				<div class="alert alert-info">
					Select the type of records you want to export from this datasource. 
				</div>			
				<form class="form-vertical" id="data_source_export_form">
					<fieldset>
						<label><b>Selection form</b> </label>
						<label class="checkbox"><input type="checkbox" name="ro_class" value="activity" checked="checked" /><?php echo readable('activity');?></label>
						<label class="checkbox"><input type="checkbox" name="ro_class" value="collection" checked="checked" /><?php echo readable('Collection');?></label>
						<label class="checkbox"><input type="checkbox" name="ro_class" value="party" checked="checked" /><?php echo readable('Party');?></label>
						<label class="checkbox"><input type="checkbox" name="ro_class" value="service" checked="checked" /><?php echo readable('Service');?></label>
						<hr/>
						<label class="checkbox"><input type="checkbox" name="ro_status" value="PUBLISHED" checked="checked" /><?php echo readable('PUBLISHED');?></label>
						<label class="checkbox"><input type="checkbox" name="ro_status" value="APPROVED" checked="checked" /><?php echo readable('APPROVED');?></label>
						<label class="checkbox"><input type="checkbox" name="ro_status" value="DRAFT" checked="checked" /><?php echo readable('DRAFT');?></label>
						<label class="checkbox"><input type="checkbox" name="ro_status" value="SUBMITTED_FOR_ASSESSMENT" checked="checked" /><?php echo readable('SUBMITTED_FOR_ASSESSMENT');?></label>
						<label class="checkbox"><input type="checkbox" name="ro_status" value="MORE_WORK_REQUIRED" checked="checked" /><?php echo readable('MORE_WORK_REQUIRED');?></label>
                        <label class="checkbox"><input type="checkbox" name="ro_status" value="ASSESSMENT_IN_PROGRESS" checked="checked"/><?php echo readable('assessment_in_progress');?></label>
	                    <hr/>
                        <label><b>Export As:</b></label>
                        <label class="checkbox"><input type="radio" name="format" checked="checked" value="rif-cs"/> RIF-CS</label>
                        <label class="checkbox"><input type="radio" name="format" value="dci"/> DCI</label>
                    </fieldset>
				</form>
			</div>
		</div>
		<div class="modal-footer">
			<a href="javascript:;" class="btn btn-primary" ng-click="export('file')"><i class="icon icon-white icon-download"></i> Download As File</a>
			<a href="javascript:;" class="btn btn-link" ng-click="export('xml')">View Records in Browser</a>
			<a href="#" class="btn hide" data-dismiss="modal">Close</a>
		</div>
	</div>

	<div class="modal hide fade" id="harvester_error_modal">
		<div class="modal-header">
			<a href="javascript:;" class="close" data-dismiss="modal">×</a>
			<h3>Data Source Harvest error</h3>
		</div>
		<div class="modal-body">
			<div ng-show="showing_error.harvester_error_type=='HARVESTER_ERROR'">
				<h4>Harvester Error</h4>
				<p><b>There was a problem communicating with the data source provider. </b></p>
				<p>
					<b>Ensure that</b>
					<ul class="padded_list">
						<li>The URL you provided is valid (including http:// or https://). </li>
						<li>The URL you provided is available on the internet (i.e. not an internal intranet link). </li>
						<li>If an OAI-PMH provider, the response is a valid OAI response. </li>
						<li>If a HTTPS provider, the server is NOT using self-signed certificates. </li>
					</ul>
				</p>
			</div>

			<div ng-show="showing_error.harvester_error_type=='DOCUMENT_LOAD_ERROR'">
				<h4>Document Load Error</h4>
				<p><b>Your XML document failed to load, as the XML may not be correctly formed. </b></p>
				<p>
					<b>Ensure that</b>
				<ul class="padded_list">
					<li>All XML tags are nested properly.</li>
					<li>All your records are structured correctly.</li>
					<li>All your parent and child nodes are correctly formatted.</li>
					<li>All your objects have been closed correctly. For example if you have an open tag <code>&lt;key&gt;</code> ensure you have a closed tag <code>&lt;/key&gt;</code>.</li>
					<li><code>xmlns:xsi="http://www.w3.org/2001/XMLSchemainstance"</code> has been defined prior to the <code>xml:schemaLocation</code>, within your XML document.</li>
				</ul></p>
				<p>
					<b>References</b>
					<ul class="normal">
						<li><a href="http://www.w3.org/TR/REC-xml/" target="_blank">XML Specifications</a></li>
						<li><a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank">ANDS Content Providers Guide</a></li>
						<li><a href="http://www.tizag.com/xmlTutorial/xmlparent.php" target="_blank">XML Parent information</a></li>
						<li><a href="http://www.tizag.com/xmlTutorial/xmlchild.php" target="_blank">XML Child information</a></li>
						<li><a href="http://services.ands.org.au/documentation/rifcs/schemadocs/registryObjects.html" target="_blank">RIF-CS Schema Documentation</a></li>
					</ul>
				</p>
			</div>

			<div ng-show="showing_error.harvester_error_type=='DOCUMENT_VALIDATION_ERROR'">
				<h4>Document Validation Error</h4>
				<p><b>Your XML document failed to validate against the RIF-CS schema </b></p>
				<p>
					<b>Ensure that</b>
					<ul class="padded_list">
						<li>All the records within your XML document have all the required elements and their associated attributes.</li>
						<li>All registry object elements and their associated attributes are correctly spelled and labelled.</li>
						<li>All the records within your XML document contain ONLY valid RIF-CS elements.</li>
						<li>Your XML file does not contain any invalid characters.</li>
					</ul>
				</p>
				<p>
					<b>References</b>
					<ul class="normal">
						<li><a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank">ANDS Content Providers Guide</a></li>
						<li><a href="http://services.ands.org.au/documentation/rifcs/schemadocs/registryObjects.html" target="_blank">RIF-CS Schema Documentation</a></li>
					</ul>
				</p>
			</div>

			<hr>
			<pre>{{showing_error.log}}</pre>
		</div>
	</div>

</div>

<div id="settings_template" class="hide">
	<div class="content-header">
		<h1>{{ds.title}}</h1>
		<ul class="nav nav-pills">
			<li class="view page-control"><a href="#!/view/{{ds.id}}">Dashboard</a></li>
			<li class="mmr page-control"><a href="<?=base_url('data_source/manage_records/');?>/{{ds.id}}">Manage Records</a></li>
			<li class="report page-control"><a href="<?=base_url('data_source/report/');?>/{{ds.id}}">Reports</a></li>
			<li class="active settings page-control"><a href="#!/settings/{{ds.id}}">Settings</a></li>
		</ul>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'tip'=>'Go to Home'))?>
		<a href="#!/">Manage My Data Sources</a>
		<a href="#!/view/{{ds.id}}">{{ds.title}} - Dashboard</a>
		<a href="#!/settings/{{ds.id}}" class="current">Settings</a>
		<div class="pull-right">
			<span class="label"><i class="icon-question-sign icon-white"></i> <a target="_blank" style="color:white;" href="http://services.ands.org.au/documentation/SettingsHelp/">Help</a></span>
		</div>
	</div>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<div class="widget-box">
					<div class="widget-content">
						<a href="#!/edit/{{ds.id}}" class="btn btn-primary">Edit Settings</a>
					</div>
					<div class="widget-content">
						<h4>Account Administration Information <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#account_info" target="_blank" class="muted">?</a></sup></h4>
						<dl class="dl-horizontal dl-wide">
							<dt>Key</dt><dd>{{ds.key}}</dd>
							<dt>Title</dt><dd>{{ds.title}}</dd>
							<dt>Record Owner</dt><dd>{{ds.record_owner}}</dd>
							<span ng-show="ds.contact_name"><dt>Contact Name</dt><dd>{{ds.contact_name}}</dd></span>
							<span ng-show="ds.contact_email"><dt>Contact Email</dt><dd>{{ds.contact_email}}</dd></span>
							<span ng-show="ds.notes"><dt>Notes</dt><dd>{{ds.notes}}</dd></span>
						</dl>
						<h4>Records Management Setttings</h4>
						<dl class="dl-horizontal dl-wide">
							<dt>Reverse Links <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#reverse_links" target="_blank" class="muted">?</a></sup></dt>
							<dd>
								<p><span checkbox="ds.allow_reverse_internal_links"></span> Allow reverse internal links</p>
								<p><span checkbox="ds.allow_reverse_external_links"></span> Allow reverse external links</p>
							</dd>
							<dt>Create Primary Relationships <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#primary_rels" target="_blank" class="muted">?</a></sup></dt>
							<dd><p><span checkbox="ds.create_primary_relationships"></span></p></dd>
							<dt>Manually Publish <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#man_publish" target="_blank" class="muted">?</a></sup></dt>
							<dd><p><span checkbox="ds.manual_publish"></span></p></dd>
							<dt>Quality Assessment Required <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#qa_required" target="_blank" class="muted">?</a></sup></dt>
							<dd><p><span checkbox="ds.qa_flag"></span></p></dd>
							<span ng-show="ds.assessment_notify_email_addr">
								<dt>Assessment Notification Email <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#qa_required" target="_blank" class="muted">?</a></sup></dt>
								<dd><p>{{ds.assessment_notify_email_addr}}</p></dd>
							</span>
							<span ng-show="ds.export_dci">
								<dt>Provide Records to Data Citation Index</dt>
								<dd><span checkbox="ds.export_dci"></span></dd>
							</span>
						</dl>
						<h4>Contributor Pages <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#contributor_pgs" target="_blank" class="muted">?</a></sup></h4>
						<div ng-show="ds.contributor">
							<p>{{ds.contributor.contributor_page}}</p>
							<table class="table table-hover headings-left">
								<thead><tr><th class="align-left">GROUP</th><th class="align-left">Contributor Page Key</th></tr></thead>
								<tbody>
									<tr ng-repeat="item in ds.contributor.items">
										<td>{{item.group}}</td>
										<td ng-show="item.contributor_page_key"><a href="{{item.contributor_page_link}}">{{item.contributor_page_key}}</a></td>
										<td ng-show="!item.contributor_page_key"><em>Not Managed!</em></td>
									</tr>
								</tbody>
							</table>
						</div>
						<h4>Harvester Settings <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#harvest_settings" target="_blank" class="muted">?</a></sup></h4>
						<dl class="dl-horizontal dl-wide">
							<span ng-show="ds.uri"><dt>URI</dt><dd>{{ds.uri}}</dd></span>
							<span ng-show="ds.provider_type"><dt>Provider Type</dt><dd>{{ds.provider_type}}</dd></span>
							<span ng-show="ds.harvest_method"><dt>Harvest Method</dt><dd>{{ds.harvest_method}}</dd></span>
							<span ng-show="ds.advanced_harvest_mode"><dt>Advanced Harvest Mode</dt><dd>{{ds.advanced_harvest_mode}}</dd></span>
							<span ng-show="ds.harvest_date"><dt>Harvest Date</dt><dd>{{ds.harvest_date}}</dd></span>
							<span ng-show="ds.oai_set"><dt>OAI-PMH Set</dt><dd>{{ds.oai_set}}</dd></span>
							<span ng-show="ds.harvest_frequency"><dt>Harvest Frequency</dt><dd>{{ds.harvest_frequency}}</dd></span>
						</dl>
						<h4></h4>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="edit_template" class="hide">
	<div class="content-header">
		<h1>{{ds.title}}</h1>
		<ul class="nav nav-pills">
			<li class="view page-control"><a href="#!/view/{{ds.id}}">Dashboard</a></li>
			<li class="mmr page-control"><a href="<?=base_url('data_source/manage_records/');?>/{{ds.id}}">Manage Records</a></li>
			<li class="report page-control"><a href="<?=base_url('data_source/report/');?>/{{ds.id}}">Reports</a></li>
			<li class="active settings page-control"><a href="#!/settings/{{ds.id}}">Settings</a></li>
		</ul>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'tip'=>'Go to Home'))?>
		<a href="#!/">Manage My Data Sources</a>
		<a href="#!/view/{{ds.id}}">{{ds.title}} - Dashboard</a>
		<a href="#!/settings/{{ds.id}}">Settings</a>
		<a href="#!/edit/{{ds.id}}" class="current">Edit Settings</a>
		<div class="pull-right">
			<span class="label"><i class="icon-question-sign icon-white"></i> <a target="_blank" style="color:white;" href="http://services.ands.org.au/documentation/SettingsHelp/">Help</a></span>
		</div>
	</div>
	<div class="container-fluid" ng-show="ds">
		<div class="row-fluid">
			<div class="widget-box">
				<div class="widget-title">
					<ul class="nav nav-tabs">
						<li ng-class="{'admin':'active'}[tab]"><a href="" ng-click="tab='admin'">Account Administration Information</a></li>
						<li ng-class="{'records':'active'}[tab]"><a href="" ng-click="tab='records'">Records Management Settings</a></li>
						<li ng-class="{'harvester':'active'}[tab]" ng-show="ds.harvester_methods"><a href="" ng-click="tab='harvester'">Harvester Settings</a></li>
					</ul>
				</div>
				<div class="widget-content nopadding">
					<form class="form-horizontal">
						<div ng-show="tab=='admin'">
							<fieldset>
								<legend>Account Administration Information <sup><a href="http://ands.org.au/guides/cpguide/cpgdsaaccount.html#accountadmin" target="_blank" class="muted">?</a></sup></legend>
								<div class="control-group">
									<label class="control-label">Key</label>
									<div class="controls">
										<span class="input-xlarge uneditable-input">{{ds.key}}</span>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Title</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="title" ng-model="ds.title">
									</div>
								</div>
								<?php if ($this->user->hasFunction('REGISTRY_SUPERUSER')):?>
									<div class="control-group">
										<label class="control-label" for="record_owner">Record Owner</label>
										<div class="controls">			
											<select data-placeholder="Choose a Record Owner" tabindex="1" class="chzn-select input-xlarge" for="record_owner" ng-model="ds.record_owner">
											<?php foreach($this->user->affiliations() as $a):?>
											<option value="<?php echo $a;?>"><?php echo $a;?></option>
											<?php endforeach;?>
											</select>
											<input type="text" class="input-small hide" name="record_owner" id="record_owner" value="{{record_owner}}">
										</div>
									</div>
								<?php endif; ?>
								<div class="control-group">
									<label class="control-label">Contact Name</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="contact_name" ng-model="ds.contact_name">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" for="contact_email">Contact Email</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="contact_email" ng-model="ds.contact_email">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" for="notes">Notes</label>
									<div class="controls">
										<textarea class="input-xxlarge" name="notes" ng-model="ds.notes"></textarea>
									</div>
								</div>

							</fieldset>
						</div>

						<div ng-show="tab=='records'">
							<fieldset>
								<legend>Records Management Settings</legend>
								<div class="control-group">
									<label class="control-label">Reverse Links <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#reverse_links" target="_blank" class="muted">?</a></sup></label>
									<div class="controls">
		    							<input type="checkbox" for="allow_reverse_internal_links" ng-model="ds.allow_reverse_internal_links">
										<p class="help-inline">Allow reverse internal links</p>
										<br>
										<input type="checkbox" for="allow_reverse_external_links" ng-model="ds.allow_reverse_external_links">
										<p class="help-inline">Allow reverse external links</p>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">
										Primary Relationships <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#primary_rels" target="_blank" class="muted">?</a></sup>
									</label>
									<div class="controls">
										<input type="checkbox" ng-model="ds.create_primary_relationships">
										<div ng-show="ds.create_primary_relationships" class="well" style="margin-top:10px;">
											<i>Data Sources can have up to 2 Primary Records <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#primary_rels" target="_blank" class="muted">?</a></sup></i>
											<div class="clearfix"></div>
											<div class="pull-left">
												<div class="control-group">
													<label class="control-label"><br/><br/>Primary Record Key</label>
													<div class="controls">
														<span class="help-block">Relate all records to:</span>
														<input dsid="ds.id" class="rosearch" type="text" class="input" ng-model="ds.primary_key_1"/>
													</div>
												</div>
												<div class="control-group">
													<label class="control-label"><br/><br/>Collection</label>
													<div class="controls">
														<span class="help-block">With the following relation types:</span>
														<input type="text" class="rifcs-type" vocab="RIFCScollectionRelationType"  placeholder="Relation Type" ng-model="ds.collection_rel_1"/>
													</div>
												</div>
												<div class="control-group">
													<label class="control-label">Service</label>
													<div class="controls">
														<input type="text" class="rifcs-type" vocab="RIFCSserviceRelationType" placeholder="Relation Type" ng-model="ds.service_rel_1"/>
													</div>
												</div>
												<div class="control-group">
													<label class="control-label">Activity</label>
													<div class="controls">
														<input type="text" class="rifcs-type" vocab="RIFCSactivityRelationType" placeholder="Relation Type" ng-model="ds.activity_rel_1"/>
													</div>
												</div>
												<div class="control-group">
													<label class="control-label">Party</label>
													<div class="controls">
														<input type="text" class="rifcs-type" vocab="RIFCSpartyRelationType" placeholder="Relation Type" ng-model="ds.party_rel_1"/>
													</div>
												</div>
											</div>
											<div class="pull-left">
													<div class="control-group">
													<label class="control-label"><br/><br/>Primary Record Key</label>
													<div class="controls">
														<span class="help-block">Relate all records to:</span>
														<input dsid="ds.id" class="rosearch" type="text" class="input" ng-model="ds.primary_key_2"/>
													</div>
												</div>
												<div class="control-group">
													<label class="control-label"><br/><br/>Collection</label>
													<div class="controls">
														<span class="help-block">With the following relation types:</span>
														<input type="text" class="rifcs-type" vocab="RIFCScollectionRelationType" placeholder="Relation Type" ng-model="ds.collection_rel_2"/>
													</div>
												</div>
												<div class="control-group">
													<label class="control-label">Service</label>
													<div class="controls">
														<input type="text" class="rifcs-type" vocab="RIFCSserviceRelationType" placeholder="Relation Type" ng-model="ds.service_rel_2"/>
													</div>
												</div>
												<div class="control-group">
													<label class="control-label">Activity</label>
													<div class="controls">
														<input type="text" class="rifcs-type" vocab="RIFCSactivityRelationType" placeholder="Relation Type" ng-model="ds.activity_rel_2"/>
													</div>
												</div>
												<div class="control-group">
													<label class="control-label">Party</label>
													<div class="controls">
														<input type="text" class="rifcs-type" vocab="RIFCSpartyRelationType" placeholder="Relation Type" ng-model="ds.party_rel_2"/>
													</div>
												</div>
											</div>
											<div class="clearfix"></div>
										</div>
									</div>	
								</div>

								<div class="control-group">
									<label class="control-label">Manually Publish Records
										<sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#man_publish" target="_blank" class="muted">?</a></sup>
									</label>
									<div class="controls">
	    								<input type="checkbox" for="manual_publish" ng-model="ds.manual_publish">
									</div>
								</div>

								<?php if ($this->user->hasFunction('REGISTRY_STAFF')): ?>
									<div class="control-group">
										<label class="control-label">Quality Assessment Required
											<sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#qa_required" target="_blank" class="muted">?</a></sup>
										</label>
										<div class="controls">
			    							<input type="checkbox" for="qa_flag" ng-model="ds.qa_flag">    																
										</div>
									</div>

									<div class="control-group">
										<label class="control-label">Assessment Notification Email</label>
										<div class="controls">
											<input type="text" class="input-xlarge" ng-model="ds.assessment_notify_email_addr">
										</div>
									</div>

									<div class="control-group">
										<label class="control-label">Provide Records to Data Citation Index
										</label>
										<div class="controls">
			    							<input type="checkbox" for="export_dci" ng-model="ds.export_dci">    																
										</div>
									</div>
								<?php endif; ?>
							</fieldset>
						</div>

						<div ng-show="tab=='harvester' && ds.harvester_methods">
							<fieldset>
								<legend>Harvester Settings
									<sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#harvest_settings" target="_blank" class="muted">?</a></sup>
								</legend>

								<div class="control-group">
									<label class="control-label" for="harvest_method">Harvest Method</label>
									<div class="controls">
										<select ng-model="ds.harvest_method" ng-options="item.id as item.title for item in ds.harvester_methods.harvester_config.harvester_methods"></select>
										<p class="help-inline">{{harvest_method_desc}}</p>
									</div>
								</div>

								<div class="control-group" ng-show="ds_crosswalk">
									<label class="control-label" for="xsl_file">Data Source Crosswalk</label>
									<div class="controls">
										<input type="text" class="input-xlarge uneditable-input" ng-model="ds.xsl_file"/>
									</div>
								</div>

								<div class="control-group" ng-show="harvest_params.uri">
									<label class="control-label" for="uri">URI</label>
									<div class="controls">
										<input type="text" class="input-xxlarge" name="uri" ng-model="ds.uri">
									</div>
								</div>

								<div class="control-group" ng-show="harvest_params.oai_set">
									<label class="control-label" for="oai_set">OAI Set</label>
									<div class="controls">
										<input type="text" class="input-normal" name="oai_set" ng-model="ds.oai_set">
									</div>
								</div>
	

								<div class="control-group">
									<label class="control-label" for="provider_type" ng-show="ds.harvest_method=='PMHHarvester'">Metadata Prefix</label>
									<label class="control-label" for="provider_type" ng-show="ds.harvest_method=='CSWHarvester'">Output Schema</label>
									<label class="control-label" for="provider_type" ng-show="ds.harvest_method=='CKANHarvester' || ds.harvest_method=='GETHarvester'">Provider Type</label>
									<div class="controls">
										<select ng-model="ds.provider_type" ng-options="item.value as item.name for item in provider_types"></select>
									</div>
								</div>

								<div class="control-group">
									<div class="controls">
										<table>
											<tr ng-repeat="cr in ds.crosswalks">
												<td>
													<span class="badge badge-success" ng-show="cr.active && cr.type!='support'" ng-click="ds.provider_type=cr.prefix">Active</span>
													<span class="badge" ng-show="!cr.active && cr.type!='support'" ng-click="ds.provider_type=cr.prefix">Inactive</span>
													<span class="badge" ng-show="!cr.active && cr.type=='support'">Supporting</span>
												</td>
												<td style="width:265px">
													<span ng-hide="cr.path">
														<input type="file" name="file" onchange="angular.element(this).scope().uploadFile(this.files)" style="line-height:0px;"/>
													</span>
													<span ng-show="cr.path">
														<b>{{cr.path}}</b> 
														<a href="{{real_base_url}}assets/uploads/harvester_crosswalks/{{ds.id}}/{{cr.path}}" target="_blank">View/Download</a>
													</span>
												</td>
												<td>
													<span ng-show="cr.type!='support'">
														<label style="width:auto;" class="control-label" for="provider_type" ng-show="ds.harvest_method=='PMHHarvester'">Metadata Prefix</label>
														<label style="width:auto;"class="control-label" for="provider_type" ng-show="ds.harvest_method=='CSWHarvester'">Output Schema</label>
														<label style="width:auto;"class="control-label" for="provider_type" ng-show="ds.harvest_method=='CKANHarvester' || ds.harvest_method=='GETHarvester'">Provider Type</label>
														<input style="margin-left:5px;" type="text" ng-model="cr.prefix">
													</span>
												</td>
												<td>
													<a href="javascript:;" tip="Remove" ng-click="removeFromList(ds.crosswalks, $index)"><i class="icon icon-remove"></i></a>
												</td>
											</tr>
										</table>
									</div>
								</div>
								

								<div class="control-group">
									<div class="controls">
										<a class="btn btn-primary" ng-click="addCrosswalk(ds, 'crosswalk')"><i class="icon icon-white icon-plus"></i> Add Crosswalk</a>		
										<a class="btn" ng-click="addCrosswalk(ds,'support')"><i class="icon icon-plus"></i> Add Supporting File</a>		
										<a href="javascript:void;"><i class="icon icon-question-sign" tip="Files uploaded can be an xml or xsl file"></i></a>
									</div>
								</div>

								<div class="control-group" ng-show="ds.harvest_method=='RIF'">
									<label class="control-label" for="oai_set">OAI Set</label>
									<div class="controls">
										<input type="text" class="input-xlarge" name="oai_set" ng-model="oai_set">
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" for="advanced_harvest_mode">Advanced Harvest Mode</label>
									<div class="controls">
										<select ng-model="ds.advanced_harvest_mode" ng-options="item.value as item.name for item in adv_harvest_modes"></select>
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" for="harvest_date">Harvest Date</label>
									<div class="controls">																					
										<div class="input-append">						
											<input type="text" class="input-xlarge datepicker" name="harvest_date" ng-model="ds.harvest_date" ng-change="alert"/>
											<span class="add-on">
										      <i data-time-icon="icon-time" data-date-icon="icon-calendar" class="icon-calendar"></i>
										    </span>				
										</div>
										<a href="" tip="Remove harvest date" ng-click="ds.harvest_date=''"><i class="icon icon-remove"></i></a>
									</div>
								</div>

								<div class="control-group">
									<label class="control-label" for="harvest_frequency">Harvest Frequency</label>
									<div class="controls">
										<select data-placeholder="Choose a Harvest Frequency" tabindex="1" class="chzn-select input-xlarge" ng-model="ds.harvest_frequency">
											<option value="">once only</option>
											<?php if ($this->user->hasFunction('REGISTRY_SUPERUSER')) { echo '<option value="hourly">hourly</option>'; } ?>
											<option value="daily">daily</option>
											<option value="weekly">weekly</option>
											<option value="fortnightly">fortnightly</option>
											<option value="monthly">monthly</option>
										</select>
									</div>
								</div>

							</fieldset>
						</div>
						<div class="form-actions">
							<a href="" class="btn btn-primary" ng-click="save()">Save</a>
							<a href="#!/view/{{ds.id}}" class="btn btn-link">Cancel</a>		
							<br>
							<div class="alert alert-{{msg.type}}" style="margin-top:10px" ng-show="msg">
								{{msg.msg}}
							</div>
						</div>

					</form>
				</div>
			</div>
		</div>
	</div>

	<div class="modal hide" id="modal">
	  <div class="modal-header">
	    <button type="button" class="close" data-dismiss="modal">×</button>
	    <h3>{{modal.title}}</h3>
	  </div>
	  <div class="modal-body">
	  	<div ng-bind-html-unsafe="modal.body"></div>
	  </div>
	  <div class="modal-footer">
	    <a href='' class='btn' data-dismiss='modal'>OK</a>
	  </div>
	</div>

</div>

<?php $this->load->view('footer'); ?>