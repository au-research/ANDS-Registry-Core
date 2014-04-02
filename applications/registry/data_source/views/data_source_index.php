<?php 

/**
 * Core Data Source Template File
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */
?>
<?php $this->load->view('header');?>
<div id="content" style="margin-left:0px">

<section id="browse-datasources" class="hide">
	<div class="content-header">
		<h1>Manage My Data Sources</h1>
		<?php if($this->user->hasFunction('REGISTRY_SUPERUSER')): ?>
			<div class="btn-group">
				<a class="btn btn-small" id="open_add_ds_form" data-toggle="modal" href="#AddNewDS"><i class="icon-plus"></i> Add New Data Source</a>
			</div>
		<?php endif; ?>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'tip'=>'Go to Home'))?>
		<?php echo anchor('data_source/manage', 'Manage My Data Sources', array('class'=>'current'))?>
	</div>
	<div class="container-fluid">
		<div class="row-fluid">
			<select data-placeholder="Choose a Data Source to View" tabindex="1" class="chzn-select" id="datasource-chooser">
				<option value=""></option>
				<?php
					foreach($dataSources as $ds){
						echo '<option value="'.$ds['id'].'">'.$ds['title'].'</option>';
					}
				?>
			</select>	
			<?php if(count($dataSources)<16)
				{
					$displayCount= count($dataSources);
				}else{
					$displayCount= 16;
				}?>
			<span class="help-block help-inline"><em>Displaying <?php echo $displayCount ?>  of <?php echo count($dataSources) ?>  data sources. Use the drop down list to view other data source(s).</em></span>
		</div>
		<div class="row-fluid">
			<ul class="lists" id="items"></ul>
		</div>

	</div>

	<div class="modal hide fade" id="AddNewDS">
	
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

			</div>
		</div>
		<div class="modal-footer">
			<a id="AddNewDS_confirm" href="javascript:;" class="btn btn-primary" data-loading-text="Saving...">Save</a>
			<a href="#" class="btn hide" data-dismiss="modal">Close</a>
		</div>
	</div>
</section>

<section id="view-datasource" class="hide"></section>
<section id="settings-datasource" class="hide"></section>
<section id="edit-datasource" class="hide"></section>

</div>
<!-- end of main content container -->


<div class="modal hide" id="logModal">
	<div class="modal-header"><button type="button" class="close" data-dismiss="modal">×</button>
		<h4>Data Source Harvest error</h4>
  	</div>
  	<div class="modal-body"></div>
  	<div class="modal-footer"></div>
</div>

<section id="datasource-templates">

<!-- mustache template for list of items-->
<div class="hide" id="items-template">
	{{#items}}
		<div class="widget-box">
			<div class="widget-title">
				<h5 class="ellipsis"><a title="{{key}}" class="view" href="#!/view/{{id}}">{{title}}</a> {{#record_owner}}({{record_owner}}){{/record_owner}}</h5>
			</div>
			<div class="btn-group item-control">
	  			<button class="btn btn-small view page-control" data_source_id="{{id}}"><i class="icon-eye-open"></i> Dashboard</button>
	  			<button class="btn btn-small mmr page-control" data_source_id="{{id}}"><i class="icon-folder-open"></i> Manage Records</button>
		  		<button class="btn btn-small edit page-control" data_source_id="{{id}}"><i class="icon-edit"></i> Edit Settings</button>
			</div>

			<div class="widget-content">
				{{#counts}}
			  		{{#status}}
			  			<span class="tag goto status_{{status}}" type="status" name="{{status}}" data_source_id="{{id}}">{{name}} ({{count}})</span>
			  		{{/status}}
		  		{{/counts}}
		  		{{^counts}}
		  			<span class="tag goto status_PUBLISHED" type="status" name="PUBLISHED" data_source_id="{{id}}">Published Records (0)</span>
		  		{{/counts}}
			</div>

		</div>
	{{/items}}
</div>


<!-- Error Templates -->
<div class="hide" id="harvesterErrorTpl">

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

<div class="hide" id="documentLoadErrorTpl">
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

<div class="hide" id="validationErrorTpl">

<h4>Document Validation Error</h4>

<p><b>Your XML document failed to validate against the RIF-CS schema </b></p>

<p>
	<b>Ensure that</b>
<ul class="padded_list">
	<li>All the records within your XML document have all the required elements and their associated attributes.</li>
	<li>All registry object elements and their associated attributes are correctly spelled and labelled.</li>
	<li>All the records within your XML document contain ONLY valid RIF-CS elements.</li>
	<li>Your XML file does not contain any invalid characters.</li>
</ul></p>

<p>
	<b>References</b>
<ul class="normal">
	<li><a href="http://ands.org.au/guides/content-providers-guide.html" target="_blank">ANDS Content Providers Guide</a></li>
	<li><a href="http://services.ands.org.au/documentation/rifcs/schemadocs/registryObjects.html" target="_blank">RIF-CS Schema Documentation</a></li>
</ul>
</p>

</div>

<script type="text/x-mustache" id="data_source_logs_template">
{{#items}}
	<li class="{{type}}">
		<a href="javascript:;" class="expand_log {{type}}"><i class="icon-list-alt"></i>{{log_snippet}} <span class="label">{{date_modified}}</span></a>
		<div class="log hide">			
			 {{#harvester_error_type}}
			 	<img data-error-type="{{harvester_error_type}}" style="float:right; cursor:pointer;" class="more_error_detail {{harvester_error_type}}" src="<?=asset_url('img/Question-mark-icon.png','base');?>" alt="more about this error" />
			 {{/harvester_error_type}}
			<pre style="width:95%; float:left;">{{log}}</pre>
			<br class="clear"/>
		</div>
	</li>
{{/items}}
</script>


<!-- mustache template for data source view single-->
<script type="text/x-mustache"  id="data-source-view-template">
<?php
	$data_source_view_fields = array(
		'key' => 'Key',
		'title' => 'Title',
		'record_owner' => 'Record Owner',
		'contact_name' => 'Contact Name',
		'contact_email' => 'Contact Email',
		'notes' => 'Notes',
		'created_when' => 'Created When',
		'created_who' => 'Created Who',
		'modified_when' => 'Modified When',
		'modified_who'	=> 'Modified Who',
		'updated' => 'Updated'
	);
?>

{{#item}}
<div class="content-header">
	<h1>{{title}}</h1>
	<ul class="nav nav-pills">
		<li class="active view page-control" data_source_id="{{data_source_id}}"><a href="<?=base_url('data_source/manage#!/view/');?>/{{data_source_id}}">Dashboard</a></li>
		<li class="mmr page-control" data_source_id="{{data_source_id}}"><a href="<?=base_url('data_source/manage_records/');?>/{{data_source_id}}">Manage Records</a></li>
		<li class="report page-control" data_source_id="{{data_source_id}}"><a href="<?=base_url('data_source/report/');?>/{{data_source_id}}">Reports</a></li>
		<li class="settings page-control" data_source_id="{{data_source_id}}"><a href="<?=base_url('data_source/manage#!/settings/');?>/{{data_source_id}}">Settings</a></li>
	</ul>
</div>

<div id="breadcrumb">
	<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'tip'=>'Go to Home'))?>
	<?php echo anchor('data_source/manage', 'Manage My Data Sources')?>
	<a href="javascript:;" class="current">{{title}} - Dashboard</a>
	<div class="pull-right">
		<span class="label"><i class="icon-question-sign icon-white"></i> <a target="_blank" style="color:white;" href="http://services.ands.org.au/documentation/DashboardHelp/">Help</a></span>
	</div>
</div>

<div class="container-fluid">
	<div class="row-fluid">

		<div class="span8" id="data_source_view_container" data_source_id="{{data_source_id}}">

		 			<div class="btn-toolbar">
						<div class="btn-group">
					  		<button class="btn edit page-control" data_source_id="{{data_source_id}}"><i class="icon-edit"></i> Edit Settings</button>
					  		<button class="btn mmr page-control" data_source_id="{{data_source_id}}"><i class="icon-folder-open"></i> Manage Records</button>
					  		<button class="btn mdr page-control" data_source_id="{{data_source_id}}"><i class="icon-time"></i> View Deleted Records</button>
							
						</div>
						<div class="btn-group pull-right">
							<a class="btn dropdown-toggle ExportDataSource" data-toggle="modal" href="#exportDataSource" id="exportDS">
								 Export Records
							</a>						
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
				
			{{#harvester_status}}
				<div class="widget-box">
					<div class="widget-content alert">A harvest is scheduled for <b>{{next_harvest}}</b> <button class="btn delete delete_harvest pull-right" data_source_id="{{data_source_id}}" harvest_id="{{id}}"><i class="icon-remove"></i> Cancel Harvest</button></div>	
				</div>
			{{/harvester_status}}

			<div class="widget-box">
				<div class="widget-title">
					<h5 id="activity_log_title">Activity Log</h5>
					<div id="activity_log_switcher" class="pull-right" style="margin-left:10px; margin-top:4px; margin-right:4px; line-height:16px;">
						<select class="log-type btn-mini">
							<option value="all">All Logs</option>
							<option value="error">Errors</option>
						</select>
					</div>
				</div>
				<div class="widget-content nopadding">
					<ul class="activity-list" id="data_source_log_container"></ul>
					<ul class="activity-list">
						<li class="viewall"><a id='show_more_log' class="tip-top" href="javascript:;" data-original-title="View all posts">Show More<i class='icon-arrow-down'></i> <span class="label label-info" id="log_summary"></span></a></li>
					</ul>
				</div>
		 
		    </div>
		</div>

		<div class="span4">
			<div class="widget-box">
				<div class="widget-title"><h5>Data Source Status Summary</h5></div>
				<div class="widget-content nopadding">
					<ul class="ro-list">
						{{#statuscounts}}
					  		{{#status}}
					  			<li class="status_{{status}}" name="{{status}}" type="status"><span class="name">{{name}}</span><span class="num">{{count}}</span></li>
					  		{{/status}}
				  		{{/statuscounts}}
					</ul>
				</div>
			</div>

			<div class="widget-box">
				<div class="widget-title"><h5>Data Source Class Summary</h5></div>
				<div class="widget-content nopadding">
					<ul class="ro-list">
						{{#classcounts}}
					  		{{#class}}
					  			<li class="" name="{{class}}" type="class"><span class="name"><img tip="{{class}}" src="<?php echo asset_url('img/{{class}}.png', 'base');?>"/> {{name}}</span> <span class="num">{{count}}</span></li>
					  		{{/class}}
				  		{{/classcounts}}
					</ul>
				</div>
			</div>

			<div class="widget-box">
				<div class="widget-title"><h5>Data Source Quality Summary</h5></div>
				<div class="widget-content nopadding">
					<ul class="ro-list">
						{{#qlcounts}}
					  		{{#level}}
					  			<li class="ql_{{level}}" name="{{level}}" type="quality_level"><span class="name">{{title}}</span> <span class="num">{{count}}</span></li>
					  		{{/level}}
				  		{{/qlcounts}}
					</ul>
				</div>
			</div>

			<?php if ($this->user->hasFunction('REGISTRY_SUPERUSER')): ?>
	  			<button class="btn btn-danger pull-right" data_source_title="{{title}}" data_source_id="{{data_source_id}}" id="delete_data_source_button"> <i class="icon-white icon-warning-sign"></i> Delete Data Source <i class="icon-white icon-trash"></i> </button>
			<?php endif; ?>

		</div>
	</div>
</div>


<!-- Modal form for importing records from a URL -->
<div class="modal hide fade" id="importRecordsFromURLModal">

	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Import Registry Objects from a URL</h3>
	</div>

	<div class="modal-screen-container">
		<div name="selectionScreen" class="modal-body">

			<div class="alert alert-info">Import registry objects from a test feed or RIFCS XML file.</div>

			<form class="form-horizontal">
				<label class="control-label">URL to import records from:</label>
				<div class="controls">
					<input type="text" name="url" placeholder="http://" />
					<p class="help-block">
						<small>Use full URL format (including http://)</small>
					</p>
				</div>
			</form>
			<p></p>
			<p>
				<span class="label label-info">Note</span>
				<small>
					This tool will import RIFCS from an XML file using an Advanced Harvest Mode of Standard. For all other operations (such as OAI-PMH), please configure a Harvest from the Data Source Settings page. 
				</small>
			</p>
		</div>
		<!-- A hidden loading screen -->
		<div name="loadingScreen" class="modal-body hide loading">
				<b>Loading XML from: </b><div id="remoteSourceURLDisplay"></div>
				<div class="progress progress-striped active">
				  <div class="bar" style="width: 100%;"></div>
				</div>
		</div>
		
		<!-- A hidden loading screen -->
		<div name="resultScreen" class="modal-body hide loading">
		</div>
	</div>
	
	
	<div class="modal-footer">
		<a href="javascript:;" class="btn btn-primary doImportRecords" data-loading-text="Importing records...">Import Records</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
	
</div>

<!-- Modal form for importing records from a URL -->
<div class="modal hide fade" id="importRecordsFromXMLModal">

	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Import Registry Objects from pasted contents</h3>
	</div>

	<div class="modal-screen-container">
		<div name="selectionScreen" class="modal-body">

			<div class="alert alert-info">Paste the registry object contents into the field below</div>

			<form class="form-vertical">
				<fieldset>
					<label> <b>Data to import:</b>
					</label>
					<textarea name="xml" id="xml_paste" rows="18" style="width:97%;font-family:Courier;font-size:8px;line-height:9px;"></textarea>
				</fieldset>
			</form>

			<p></p>
			<p>
				<span class="label label-info">Note</span>
				<small>
					This tool will import pasted RIFCS contents using an Advanced Harvest Mode of Standard. For all other operations (such as OAI-PMH), please configure a Harvest from the Data Source Settings page. 
				</small>
				<small>
					This tool is designed for small imports (&lt;100 records). It may fail with larger bulk imports.
				</small>
			</p>
			
		</div>

		<!-- A hidden loading screen -->
		<div name="loadingScreen" class="modal-body hide loading"> <b>Loading XML from:</b>
			<div id="remoteSourceURLDisplay"></div>
			<div class="progress progress-striped active">
				<div class="bar" style="width: 100%;"></div>
			</div>
		</div>

		<!-- A hidden loading screen -->
		<div name="resultScreen" class="modal-body hide loading"></div>
	</div>

	<div class="modal-footer">
		<a href="javascript:;" class="btn btn-primary doImportRecords" data-loading-text="Importing records...">Import Records</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
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
				</fieldset>
			</form>
		</div>
	</div>
	
	<div class="modal-footer">
		<a href="javascript:;" class="btn btn-primary exportRecord" type="xml" data-loading-text="Fetching records...">View RIF-CS in Browser</a>
		<a href="javascript:;" class="btn btn-primary exportRecord" type="file" data-loading-text="Fetching records...">Download RIF-CS</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
	
</div>

	{{/item}}
</script>

<script type="text/x-mustache"  id="data-source-settings-template">
<?php
	$data_source_view_fields = array(
		'key' => 'Key',
		'title' => 'Title',
		'record_owner' => 'Record Owner',
		'contact_name' => 'Contact Name',
		'contact_email' => 'Contact Email',
		'notes' => 'Notes',
		//'created' => 'Created When',
		//'created_who' => 'Created Who'
	);
?>

	{{#item}}
	<div class="content-header">
		<h1>{{title}}</h1>
		<ul class="nav nav-pills">
			<li class="view page-control" data_source_id="{{data_source_id}}"><a href="<?=base_url('data_source/manage#!/view/');?>/{{data_source_id}}">Dashboard</a></li>
			<li class="mmr page-control" data_source_id="{{data_source_id}}"><a href="<?=base_url('data_source/manage_records/');?>/{{data_source_id}}">Manage Records</a></li>
			<li class="report page-control" data_source_id="{{data_source_id}}"><a href="<?=base_url('data_source/report/');?>/{{data_source_id}}">Reports</a></li>
			<li class="active settings page-control" data_source_id="{{data_source_id}}"><a href="<?=base_url('data_source/manage#!/settings/');?>/{{data_source_id}}">Settings</a></li>
		</ul>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'tip'=>'Go to Home'))?>
		<?php echo anchor('data_source/manage', 'Manage My Data Sources')?>
		<a href="#!/view/{{data_source_id}}">{{title}} - Dashboard</a>
		<a href="javascript:;" class="current">Settings</a>
		<div class="pull-right">
			<span class="label"><i class="icon-question-sign icon-white"></i> <a target="_blank" style="color:white;" href="http://services.ands.org.au/documentation/SettingsHelp/">Help</a></span>
		</div>
	</div>
<div class="container-fluid">
<div class="row-fluid">

	
	<div class="span12" id="data_source_view_container" data_source_id="{{data_source_id}}">
		<div class="widget-box">
	    	
	 		<div class='widget-content'>
	 			<a href="javascript:;" class="page-control edit btn btn-primary" data_source_id="{{data_source_id}}">Edit Settings</a>
	 		</div>
	 
	    	<div class="widget-content">

				<h4>Account Administration Information <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#account_info" target="_blank" class="muted">?</a></sup></h4>
				<dl class="dl-horizontal dl-wide">
					<?php 
					foreach($data_source_view_fields as $key=>$name){
						echo '{{#'.$key.'}}';
						echo '<dt>'.$name.'</dt>';
						echo '<dd>{{'.$key.'}}&nbsp;</dd>';
						echo '{{/'.$key.'}}';
						echo '{{^'.$key.'}}';
						echo '<dt>'.$name.'</dt>';
						echo '<dd><i>not configured</i></dd>';
						echo '{{/'.$key.'}}';
					}
					?>
			 	</dl>
			 	<h4>Records Management Settings</h4>
			 	<dl class="dl-horizontal dl-wide">
					<dt>Reverse Links <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#reverse_links" target="_blank" class="muted">?</a></sup></dt>
					<dd>
						<p>
							
							<div class="checkbox_view{{allow_reverse_internal_links}}">Allow reverse internal links </div> 
					
						</p>
						<p>
			
							<div class="checkbox_view{{allow_reverse_external_links}}"> Allow reverse external links</div>
						</p>
					</dd>

					{{#create_primary_relationships}}
					<dt>Create Primary Relationships <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#primary_rels" target="_blank" class="muted">?</a></sup></dt>
					<dd>
						<p>
							<div class="checkbox_view{{create_primary_relationships}}"> </div>
						</p>
					</dd>
					{{/create_primary_relationships}}
					
					{{#manual_publish}}
					<dt>Manually Publish <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#man_publish" target="_blank" class="muted">?</a></sup></dt>
					<dd>
						<p>
							<div class="checkbox_view{{manual_publish}}">		
															</div>
						</p>
					</dd>
					{{/manual_publish}}

					{{#qa_flag}}
					<dt>Quality Assessment Required <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#qa_required" target="_blank" class="muted">?</a></sup></dt>
					<dd>
						<p>
							<div class="checkbox_view{{qa_flag}}">			</div>
						</p>
					</dd>
					{{/qa_flag}}

					{{#assessment_notify_email_addr}}
					<dt>Assessment Notification Email <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#qa_required" target="_blank" class="muted">?</a></sup></dt>
					<dd>
						<p>
							{{assessment_notify_email_addr}}
						</p>
					</dd>
					{{/assessment_notify_email_addr}}
					
		 		</dl>

		 		<p>&nbsp;</p>
			 	<h4>Contributor Pages <span class="label"><a href="http://services.ands.org.au/documentation/SettingsHelp/#contributor_pgs" class="white" target="_blank"><i class="icon icon-white icon-question-sign"></i></a></span></h4> 
				<div class="" id="contributor_groups"></div>
			 	
			 
			 	<h4>Harvester Settings <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#harvest_settings" target="_blank" class="muted">?</a></sup></h4>
			 	<dl class="dl-horizontal dl-wide">
			 		{{#uri}}
					<dt>URI</dt>
					<dd>{{uri}}&nbsp;</dd>
					{{/uri}}

					{{#provider_type}}
					<dt>Provider Type</dt>
					<dd>{{provider_type}}</dd>
					{{/provider_type}}

					{{#harvest_method}}
					<dt>Harvest Method</dt>
					<dd>{{harvest_method}}</dd>
					{{/harvest_method}}

					{{#advanced_harvest_mode}}
					<dt>Advanced Harvest Mode</dt>
					<dd>{{advanced_harvest_mode}}</dd>
					{{/advanced_harvest_mode}}
					
					{{#harvest_date}}
					<dt>Harvest Date</dt>
					<dd>{{harvest_date}}</dd>
					{{/harvest_date}}

					{{#oai_set}}
					<dt>OAI-PMH Set</dt>
					<dd>{{oai_set}}</dd>
					{{/oai_set}}


					{{#harvest_frequency}}
					<dt>Harvest Frequency</dt>
					<dd>{{harvest_frequency}}</dd>
					{{/harvest_frequency}}
			 	</dl>
			</div>
	    </div>
	</div>

</div>


</div>

<!-- Modal form for importing records from a URL -->
<div class="modal hide fade" id="importRecordsFromURLModal">

	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Import Registry Objects from a URL</h3>
	</div>

	<div class="modal-screen-container">
		<div name="selectionScreen" class="modal-body">

			<div class="alert alert-info">Import registry objects from a test feed or backup.</div>

			<form class="form-horizontal">
				<label class="control-label">URL to import records from:</label>
				<div class="controls">
					<input type="text" name="url" placeholder="http://" />
					<p class="help-block">
						<small>Use full URL format (including http://)</small>
					</p>
				</div>
			</form>

			<p>
				<span class="label label-info">Note</span>
				<small>
					This tool does not support OAI-PMH. You must use the Harvester to import from an OAI-PMH feed.
				</small>
			</p>
		</div>
		<!-- A hidden loading screen -->
		<div name="loadingScreen" class="modal-body hide loading">
				<b>Loading XML from: </b><div id="remoteSourceURLDisplay"></div>
				<div class="progress progress-striped active">
				  <div class="bar" style="width: 100%;"></div>
				</div>
		</div>
		
		<!-- A hidden loading screen -->
		<div name="resultScreen" class="modal-body hide loading">
		</div>
	</div>
	
	
	<div class="modal-footer">
		<a href="javascript:;" class="btn btn-primary doImportRecords" data-loading-text="Importing records...">Import Records</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
	
</div>

<!-- Modal form for importing records from a URL -->
<div class="modal hide fade" id="importRecordsFromXMLModal">

	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Import Registry Objects from pasted contents</h3>
	</div>

	<div class="modal-screen-container">
		<div name="selectionScreen" class="modal-body">

			<div class="alert alert-info">Paste the registry object contents into the field below</div>

			<form class="form-vertical">
				<fieldset>
					<label> <b>Data to import:</b>
					</label>
					<textarea name="xml" id="xml_paste" rows="18" style="width:97%;font-family:Courier;font-size:8px;line-height:9px;"></textarea>
				</fieldset>
			</form>

			<p>
				<span class="label label-info">Note</span>
				<small>
					This tool is designed for small imports (&lt;100 records). It may fail with larger bulk imports.
				</small>
			</p>
		</div>

		<!-- A hidden loading screen -->
		<div name="loadingScreen" class="modal-body hide loading"> <b>Loading XML from:</b>
			<div id="remoteSourceURLDisplay"></div>
			<div class="progress progress-striped active">
				<div class="bar" style="width: 100%;"></div>
			</div>
		</div>

		<!-- A hidden loading screen -->
		<div name="resultScreen" class="modal-body hide loading"></div>
	</div>

	<div class="modal-footer">
		<a href="javascript:;" class="btn btn-primary doImportRecords" data-loading-text="Importing records...">Import Records</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>

</div>


<!-- Modal form for importing records from a URL -->
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
					<input type="checkbox" name="activity" value="yes" checked="checked" />Activities<br/>
					<input type="checkbox" name="collection" value="yes" checked="checked" />Collections<br/>
					<input type="checkbox" name="party" value="yes" checked="checked" />Parties<br/>
					<input type="checkbox" name="service" value="yes" checked="checked" />Services<br/>
					<br/>
					<select name="status" data-placeholder="Choose by Status" tabindex="1" class="chzn-select input-xlarge" for="class_1">
						<option value="All">ALL status</option>
						<option value="PUBLISHED">PUBLISHED</option>
						<option value="APPROVED">APPROVED</option>
						<option value="DRAFT">DRAFT</option>
						<option value="SUBMITTED_FOR_ASSESSMENT">SUBMITTED_FOR_ASSESSMENT</option>
						<option value="MORE_WORK_REQUIRED">MORE_WORK_REQUIRED</option>
						<option value="ASSESSMENT_IN_PROGRESS">ASSESSMENT_IN_PROGRESS</option>
					</select>
					<!--label><b>Limit: </b> </label><input type="text" name="limit" value="20" /><br/-->
				</fieldset>
			</form>
		</div>
	
	</div>
	
	
	<div class="modal-footer">
		<a href="javascript:;" class="btn btn-primary exportRecord" type="xml" data-loading-text="Fetching records...">Show me the XML</a>
		<a href="javascript:;" class="btn btn-primary exportRecord" type="file" data-loading-text="Fetching records...">Get My File</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
	
</div>

	{{/item}}
</script>

<!-- Successful import screen mustache template -->
<div class="hide" id="import-screen-success-report-template">
	<div class="alert alert-success">
		{{message}}
	</div>
	
	{{#log}}
		<pre class="well linenums">{{log}}</pre>
	{{/log}}
</div>




<!-- mustache template for data source edit single-->
<script type="text/x-mustache"  id="data-source-edit-template">
{{#item}}
<input type="hidden" id="data_source_id_input" value="{{data_source_id}}"/>
	<div class="content-header">
		<h1 >{{title}}</h1>
		<ul class="nav nav-pills">
			<li class="view page-control" data_source_id="{{data_source_id}}"><a href="<?=base_url('data_source/manage#!/view/');?>/{{data_source_id}}">Dashboard</a></li>
			<li class="mmr page-control" data_source_id="{{data_source_id}}"><a href="<?=base_url('data_source/manage_records/');?>/{{data_source_id}}">Manage Records</a></li>
			<li class="report page-control" data_source_id="{{data_source_id}}"><a href="<?=base_url('data_source/report/');?>/{{data_source_id}}">Reports</a></li>
			<li class="active settings page-control" data_source_id="{{data_source_id}}"><a href="<?=base_url('data_source/manage#!/settings/');?>/{{data_source_id}}">Settings</a></li>
		</ul>
	</div>
	<div id="breadcrumb">
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'tip'=>'Go to Home'))?>
		<?php echo anchor('data_source/manage', 'Manage My Data Sources')?>
		<a href="#!/view/{{data_source_id}}" class="">{{title}} - Dashboard</a>
		<a href="javascript:;" class="current">Edit</a>
		<div class="pull-right">
			<span class="label"><i class="icon-question-sign icon-white"></i> <a target="_blank" style="color:white;" href="http://services.ands.org.au/documentation/SettingsHelp/ ">Help</a></span>
		</div>
	</div>
<div class="container-fluid">
<div class="row-fluid">

	<div class="widget-box">
		<div class="widget-title">
		    <ul class="nav nav-tabs">
		  <li class="active"><a href="#admin" data-toggle="tab">Account Administration Information</a></li>
		  <li><a href="#records" data-toggle="tab">Records Management Settings</a></li>
		  <li><a href="#harvester" data-toggle="tab">Harvester Settings</a></li>
		</ul>
		</div>
	<div class="widget-content nopadding">
		

		<form class="form-horizontal" id="edit-form">
			<div class="tab-content">
				<div id="admin" class="tab-pane active">
					<fieldset>
						<legend>Account Administration Information <sup><a href="http://ands.org.au/guides/cpguide/cpgdsaaccount.html#accountadmin" target="_blank" class="muted">?</a></sup></legend>
						<div class="control-group">
							<label class="control-label">Key</label>
							<div class="controls">
								{{key}}
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="title">Title</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="title" value="{{title}}">
								<p class="help-inline"><small></small></p>
							</div>
						</div>

						<?php if ($this->user->hasFunction('REGISTRY_SUPERUSER')):?>
						<div class="control-group">
							<label class="control-label" for="record_owner">Record Owner</label>
							<div class="controls">			
								<select data-placeholder="Choose a Record Owner" tabindex="1" class="chzn-select input-xlarge" for="record_owner" style="width72px">
								<?php foreach($this->user->affiliations() as $a):?>
								<option value="<?php echo $a;?>"><?php echo $a;?></option>
								<?php endforeach;?>
								</select>
								<input type="text" class="input-small hide" name="record_owner" id="record_owner" value="{{record_owner}}">
							</div>
						</div>
						<?php endif;?>

						<div class="control-group">
							<label class="control-label" for="contact_name">Contact Name</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="contact_name" value="{{contact_name}}">
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="contact_email">Contact Email</label>
							<div class="controls">
								<input type="text" class="input-xlarge" name="contact_email" value="{{contact_email}}">
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="notes">Notes</label>
							<div class="controls">
								<textarea class="input-xxlarge" name="notes">{{notes}}</textarea>
							</div>
						</div>
						
					</fieldset>
				</div>
				<div id="records" class="tab-pane ">
					<fieldset>
						<legend>Records Management Settings</legend>
						<div class="control-group">
							<label class="control-label">Reverse Links <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#reverse_links" target="_blank" class="muted">?</a></sup></label>
							<div class="controls">

								<div class="normal-toggle-button" value="{{allow_reverse_internal_links}}">
    								<input type="checkbox" for="allow_reverse_internal_links">
								</div>
								<p class="help-inline">Allow reverse internal links</p>
								<br/><br/>
								<div class="normal-toggle-button" value="{{allow_reverse_external_links}}">
    								<input type="checkbox" for="allow_reverse_external_links">
								</div>
								<p class="help-inline">Allow reverse external links</p>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label">
								Create Primary <br/>Relationships
								<sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#primary_rels" target="_blank" class="muted">?</a></sup>
							</label>
							<div class="controls">
								<div class="create-primary normal-toggle-button" style="margin-top:9px;" value="{{create_primary_relationships}}">
    								<input type="checkbox" for="create_primary_relationships" name="create_primary_relationships">
								</div>
							</div>
						</div>
						
						<div id="primary-relationship-form">
							<div class="well">
								<i>Data Sources can have up to 2 Primary Records <sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#primary_rels" target="_blank" class="muted">?</a></sup></i>
								<div class="clearfix"></div>
								<div class="pull-left">
									<div class="control-group hide">
										<label class="control-label">Class</label>
										<div class="controls">
											<input type="text" class="rifcs-type" vocab="RIFCSClass" name="class_1" placeholder="Class" value="n/a"/>
										</div>
									</div>
									
									<div class="control-group">
										<label class="control-label"><br/><br/>Primary Record Key</label>
										<div class="controls">
											<span class="help-block">Relate all records to:</span>
											<input type="text" class="input ro_search" name="primary_key_1" id="primary_key_1" value="{{primary_key_1}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label"><br/><br/>Collection</label>
										<div class="controls">
											<span class="help-block">With the following relation types:</span>
											<input type="text" class="rifcs-type" vocab="RIFCScollectionRelationType" name="collection_rel_1" placeholder="Relation Type" value="{{collection_rel_1}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Service</label>
										<div class="controls">
											<input type="text" class="rifcs-type" vocab="RIFCSserviceRelationType" name="service_rel_1" placeholder="Relation Type" value="{{service_rel_1}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Activity</label>
										<div class="controls">
											<input type="text" class="rifcs-type" vocab="RIFCSactivityRelationType" name="activity_rel_1" placeholder="Relation Type" value="{{activity_rel_1}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Party</label>
										<div class="controls">
											<input type="text" class="rifcs-type" vocab="RIFCSpartyRelationType" name="party_rel_1" placeholder="Relation Type" value="{{party_rel_1}}"/>
										</div>
									</div>
								</div>
								<div class="pull-left">
								<div class="control-group hide">
										<label class="control-label">Class</label>
									<div class="controls">
											<input type="text" class="rifcs-type" vocab="RIFCSClass" name="class_2" placeholder="Class" value="n/a"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label"><br/><br/>Primary Record Key</label>
										<div class="controls">
											<span class="help-block">Relate all records to:</span>
											<input type="text" class="input ro_search" name="primary_key_2" id="primary_key_2" value="{{primary_key_2}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label"><br/><br/>Collection</label>
										<div class="controls">
											<span class="help-block">With the following relation types:</span>
											<input type="text" class="rifcs-type" vocab="RIFCScollectionRelationType" name="collection_rel_2" placeholder="Relation Type" value="{{collection_rel_2}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Service</label>
										<div class="controls">
											<input type="text" class="rifcs-type" vocab="RIFCSserviceRelationType" name="service_rel_2" placeholder="Relation Type" value="{{service_rel_2}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Activity</label>
										<div class="controls">
											<input type="text" class="rifcs-type" vocab="RIFCSactivityRelationType" name="activity_rel_2" placeholder="Relation Type" value="{{activity_rel_2}}"/>
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Party</label>
										<div class="controls">
											<input type="text" class="rifcs-type" vocab="RIFCSpartyRelationType" name="party_rel_2" placeholder="Relation Type" value="{{party_rel_2}}"/>
										</div>
									</div>
								</div>
								<div class="clearfix"></div>
							</div>
						</div>

						

					<!-- This poush to NLA functionality has been excluded for release 10 as NLA are not using it
						<div class="control-group">
							<label class="control-label">Party Records to NLA</label>
							<div class="controls">
								<p class="help-inline">
								<div class="push_to_nla normal-toggle-button" value="{{push_to_nla}}">
    								<input type="checkbox" for="push_to_nla">
								</div>
								</p>
							</div>
						</div>

						<div id="nla-push-div" class="hide">
							<div class="control-group">					
								<div class="controls">	
									<p>
										ISIL: <input name="isil_value" value="{{isil_value}}"/>
									</p>
								</div>
							</div>	
						</div> -->


					{{#statuscounts}}
				  		{{#status}}
				  			<span id="{{{name}}}" class="publish_count hidden">{{count}}</span>
				  		{{/status}}
			  		{{/statuscounts}}
								
						<div class="control-group">
							<label class="control-label">Manually Publish Records
								<sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#man_publish" target="_blank" class="muted">?</a></sup>
							</label>
							<div class="controls">
								<div class="normal-toggle-button manual_publish" value="{{manual_publish}}">
    								<input type="checkbox" for="manual_publish" id="check_manual_publish">
								</div>	
							</div>
						</div>

						<span class="hidden" id="qa_flag_set">no</span>
						<?php if ($this->user->hasFunction('REGISTRY_STAFF')): ?>
							<div class="control-group">
								<label class="control-label">Quality Assessment Required
									<sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#qa_required" target="_blank" class="muted">?</a></sup>
								</label>
								<div class="controls">
									<div class="normal-toggle-button qa_flag" style="margin-top:9px;" value="{{qa_flag}}">
	    								<input type="checkbox" for="qa_flag" id="check_qa_flag">     																
									</div>
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" for="assessment_notify_email_addr">Assessment Notification Email</label>
								<div class="controls">
									<input type="text" class="input-xxlarge" name="assessment_notify_email_addr" value="{{assessment_notify_email_addr}}">
								</div>
							</div>
						<?php endif; ?>
						<div class="control-group">
							<label class="control-label" for="institution_pages">Contributor Pages
								<sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#contributor_pages" target="_blank" class="muted">?</a></sup>
							</label>
							<div class="controls">
								<input type="radio" class="contributor-page" name="institution_pages_radio" value="0"><p class="help-inline">Do not have contributor pages</p><br />
								<input type="radio" class="contributor-page" name="institution_pages_radio" value="1"><p class="help-inline">Auto generate Contributor Pages for all my groups</p><br />
								<input type="radio" class="contributor-page" name="institution_pages_radio" value="2"><p class="help-inline">Manually manage my Contributor Pages and groups</p><br />
								<input type="text" class="input-small hide" name="institution_pages"  id="institution_pages" value="{{institution_pages}}">	
								<p>
									<div class="well" id="contributor_groups2"></div>
								</p>
							</div>
						</div>
					</fieldset>
				</div>
				<div id="harvester" class="tab-pane">
					<fieldset>
						<legend>Harvester Settings
							<sup><a href="http://services.ands.org.au/documentation/SettingsHelp/#harvest_settings" target="_blank" class="muted">?</a></sup>
						</legend>
						<div class="control-group">
							<label class="control-label" for="uri">URI</label>
							<div class="controls">
								<input type="text" class="input-xxlarge" name="uri" value="{{uri}}">
							</div>
						</div>

						<div class="control-group <?php if(!$this->user->hasFunction('REGISTRY_SUPERUSER')) { echo 'hide'; }?>">
							<label class="control-label" for="provider_type">Provider Type</label>
							<div class="controls">
								<select data-placeholder="Choose a Provider Type" tabindex="1" class="chzn-select input-xlarge" for="provider_type">
									<option value=""></option>
									<option value="<?=RIFCS_SCHEME;?>">RIFCS</option>
									<?php 
									$crosswalks = getCrosswalks();
									foreach ($crosswalks AS $crosswalk)
									{
										echo '<option value="' . $crosswalk->metadataFormat() . '">' . $crosswalk->identify() . '</option>' . NL;
									}
									?>
								</select>
								<input type="text" class="input-small hide" name="provider_type" id="provider_type" value="{{provider_type}}">
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="harvest_method">Harvest Method</label>
							<div class="controls">
								<select data-placeholder="Choose a Harvest Method" tabindex="1" class="chzn-select input-xlarge" for="harvest_method" id="harvest">
									<option value="GET">DIRECT (HTTP)</option>
									<option value="PMH">Harvested (OAI-PMH)</option>
								</select>
								<input type="text" class="input-small hide" name="harvest_method" id="harvest_method" value="{{harvest_method}}">
							</div>
						</div>

						<div class="control-group" id="oai_set_container">
							<label class="control-label" for="oai_set">OAI Set</label>
							<div class="controls">
								<input type="text" class="input-xxlarge" name="oai_set" value="{{oai_set}}" length="512">
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="advanced_harvest_mode">Advanced Harvest Mode</label>
							<div class="controls">
								<select data-placeholder="Choose an Advanced Harvest Mode" tabindex="1" class="chzn-select input-xlarge" for="advanced_harvest_mode" id="advanced">
									<option value="STANDARD">Standard Mode</option>
									<option value="INCREMENTAL">Incremental Mode</option>
									<option value="REFRESH">Full Refresh Mode</option>
								</select>
								<input type="text" class="input-small hide" name="advanced_harvest_mode" id="advanced_harvest_mode" value="{{advanced_harvest_mode}}">
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="harvest_date">Harvest Date</label>
							<div class="controls">																					
								<div class="input-append">						
									<input type="text" class="input-xlarge datepicker" name="harvest_date" value="{{harvest_date}}"/>
									<span class="add-on">
								      <i data-time-icon="icon-time" data-date-icon="icon-calendar" class="icon-calendar"></i>
								    </span>								
								</div>
								<a href="javascript:;" tip="Remove harvest date" id="removeHarvestDate"><i class="icon icon-remove"></i></a>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="harvest_frequency">Harvest Frequency</label>
							<div class="controls">
								<select data-placeholder="Choose a Harvest Frequency" tabindex="1" class="chzn-select input-xlarge" for="harvest_frequency">
									<option value="">once only</option>
									<?php if ($this->user->hasFunction('REGISTRY_SUPERUSER')) { echo '<option value="hourly">hourly</option>'; } ?>
									<option value="daily">daily</option>
									<option value="weekly">weekly</option>
									<option value="fortnightly">fortnightly</option>
									<option value="monthly">monthly</option>
								</select>
								<input type="text" class="input-small hide" name="harvest_frequency" id="harvest_frequency" value="{{harvest_frequency}}">
							</div>
						</div>
						<div class="form-actions">
							<button class="btn" id="test-harvest" data-loading-text="Testing Harvest..." data_source_id="{{data_source_id}}">Test Harvest</button>
						</div>
					</fieldset>
				</div>
			</div>

			<div class="form-actions">
				<button class="btn btn-primary" id="save-edit-form" data-loading-text="Saving..." data_source_id="{{data_source_id}}">Save</button>
				<button class="btn btn-primary" id="cancel-edit-form" data-loading-text="Cancel..." data_source_id="{{data_source_id}}">Cancel</button>				
			</div>
			<div class="modal hide" id="test_harvest_activity_log">
			  <div class="modal-header">
			    <button type="button" class="close" data-dismiss="modal">×</button>
			    <h3>Activity Log</h3>
			  </div>
			  <div class="modal-body"></div>
			  <div class="modal-footer">
			    
			  </div>
			</div>
			<div class="modal hide" id="myModal">
			  <div class="modal-header">
			    <button type="button" class="close" data-dismiss="modal">×</button>
			    <h3>Alert</h3>
			  </div>
			  <div class="modal-body"></div>
			  <div class="modal-footer">
			    
			  </div>
			</div>
		</form>
</div>
</div>

	</div>
</div>
{{/item}}
</script>


</section>


<?php $this->load->view('footer');?>