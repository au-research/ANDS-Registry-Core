<?php 
/**
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
?>

<?php  $this->load->view('header');?>
<input type="hidden" value="<?php echo $ds->id;?>" id="data_source_id"/>
<div id="content" style="margin-left:0px">
	<div class="content-header">
		<h1><?php echo $ds->title;?></h1>
		<ul class="nav nav-pills">
			<li class=""><?php echo anchor('data_source/manage#!/view/'.$ds->id,'Dashboard');?></li>
			<li class="active mmr"><a href="#">Manage Records</a></li>
			<li class=""><?php echo anchor('data_source/report/'.$ds->id,'Reports');?></li>
			<li class=""><?php echo anchor('data_source/manage#!/settings/'.$ds->id,'Settings');?></li>
		</ul>
	</div>

	<div id="breadcrumb">
		<div class="pull-right">
			<span class="label"><i class="icon-question-sign icon-white"></i><a class="youtube" href="http://www.youtube.com/watch?v=cuVQfTyBbNk" style="color:white;" > New to this screen? Take a tour!</a></span>&nbsp;
			<span class="label"><i class="icon-question-sign icon-white"></i> <a target="_blank" style="color:white;" href="http://services.ands.org.au/documentation/MMRHelp/"> Help</a></span>
		</div>
		<?php echo anchor('/', '<i class="icon-home"></i> Home', array('class'=>'tip-bottom', 'title'=>'Go to Home'))?>
		<?php echo anchor('data_source/manage/', 'Manage My Data Sources');?>
		<?php echo anchor('data_source/manage#!/view/'.$ds->id, $ds->title.' - Dashboard');?>
		<a href="#" class="current">Manage Records</a>

	</div>
	
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">

				<div class="btn-toolbar mmr_toolbar">

					<form class="form-search pull-left" id="search_form">
						<div class="input-prepend">
							<button type="submit" class="btn">Search</button>
							<input type="text" class="input-medium search-query" placeholder="Keywords">
						</div>
					</form>
					
					<div class="btn-group">
						<button class="btn dropdown-toggle" data-toggle="dropdown">Sort <span class="caret"></span></button>
						<ul class="dropdown-menu">
							<li><a href="javascript:;" class="sort" sort="updated" value="">Date Modified <span class="icon"></span></a></li>
							<li><a href="javascript:;" class="sort" sort="quality_level" value="">Quality Level  <span class="icon"></span></a></li>
						</ul>
					</div>
					<div class="btn-group">
						<button class="btn dropdown-toggle" data-toggle="dropdown">Filter <span class="caret"></span></button>
						<ul class="dropdown-menu">
							<li <?php echo 'class="'.($ds->count_DRAFT > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="status" value="DRAFT">Draft (<?php echo $ds->count_DRAFT;?>)<span class="icon"></span></a></li>
							
							<?php if ($ds->qa_flag==DB_TRUE):  ?>
								<li <?php echo 'class="'.($ds->count_SUBMITTED_FOR_ASSESSMENT > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="status" value="SUBMITTED_FOR_ASSESSMENT">Submitted For Assessment (<?php echo $ds->count_SUBMITTED_FOR_ASSESSMENT;?>)<span class="icon"></span></a></li>
								<li <?php echo 'class="'.($ds->count_ASSESSMENT_IN_PROGRESS > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="status" value="ASSESSMENT_IN_PROGRESS">Assessment in Progress (<?php echo $ds->count_ASSESSMENT_IN_PROGRESS;?>)<span class="icon"></span></a></li>
							
							<?php endif; ?>

							<?php if ($ds->manual_publish==DB_TRUE):  ?>
								<li <?php echo 'class="'.($ds->count_APPROVED > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="status" value="APPROVED">Approved (<?php echo $ds->count_APPROVED;?>)<span class="icon"></span></a></li>
							<?php endif; ?>

							

							<li <?php echo 'class="'.($ds->count_PUBLISHED > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="status" value="PUBLISHED">Published (<?php echo $ds->count_PUBLISHED;?>)<span class="icon"></span></a></li>
							<li class="divider"></li>
							<li <?php echo 'class="'.($ds->count_collection > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="class" value="collection">Collections (<?php echo $ds->count_collection;?>)<span class="icon"></span></a></li>
							<li <?php echo 'class="'.($ds->count_party > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="class" value="party">Parties (<?php echo $ds->count_party;?>)<span class="icon"></span></a></li>
							<li <?php echo 'class="'.($ds->count_service > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="class" value="service">Services (<?php echo $ds->count_service;?>)<span class="icon"></span></a></li>
							<li <?php echo 'class="'.($ds->count_activity > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="class" value="activity">Activities (<?php echo $ds->count_activity;?>)<span class="icon"></span></a></li>
							<li class="divider"></li>
							<li <?php echo 'class="'.($ds->count_level_1 > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="quality_level" value="1">Quality Level 1 (<?php echo $ds->count_level_1;?>)<span class="icon"></span></a></li>
							<li <?php echo 'class="'.($ds->count_level_2 > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="quality_level" value="2">Quality Level 2 (<?php echo $ds->count_level_2;?>)<span class="icon"></span></a></li>
							<li <?php echo 'class="'.($ds->count_level_3 > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="quality_level" value="3">Quality Level 3 (<?php echo $ds->count_level_3;?>)<span class="icon"></span></a></li>
							<li <?php echo 'class="'.($ds->count_level_4 > 0 ? '' : 'disabled').'"';?>><a href="javascript:;" class="filter" name="quality_level" value="4">Gold Standard (<?php echo $ds->count_level_4;?>)<span class="icon"></span></a></li>
							<li class="divider"></li>
							<li><a href="javascript:;" class="filter" name="flag" value="t">Flagged Records <span class="icon"></span></a></li>
							<li><a href="javascript:;" class="filter" name="tag" value="1">Records with Tags <span class="icon"></span></a></li>
							
						</ul>
					</div>


					<span id="active_filters">
					</span>

					<div class="btn-group pull-right" style="margin-right:35px;">
				  		<button class="btn btn-mini mdr page-control op" action="manage_deleted_records" data_source_id="<?php echo $ds->id;?>"><i class="icon-time"></i> View Deleted Records</button><br />
				  		<a href="<?php echo base_url('registry_object/add'); ?>" class="btn btn-mini mdr page-control" data_source_id="<?php echo $ds->id;?>"><i class="icon-plus"></i>  Add New Record</a>
					</div>
				</div>
			</div>
			
			<div style="position: absolute; left: 50%;">
				<div id="status_message" class="alert alert-info hide">Loading</div>
			</div>
			
		</div>
		<div class="pool" id="mmr_hopper">
			<div class="block hide">
				<div id="MORE_WORK_REQUIRED" class='status_field'></div>
				<div id="DRAFT" class='status_field'></div>
			</div>
			<div class="block hide">
				<div id="SUBMITTED_FOR_ASSESSMENT" class='status_field'></div>
			</div>
			<div class="block hide">
				<div id="ASSESSMENT_IN_PROGRESS" class='status_field'></div>
			</div>
			<div class="block hide">
				<div id="APPROVED" class='status_field'></div>
			</div>
			<div class="block hide">
				<div id="PUBLISHED" class='status_field'></div>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
</div>

<script type="text/x-mustache" id="mmr_status_template">
<div class="widget-box ro_box" status="{{name}}">
	<div class="widget-title">
		<span class="icon selector_menu" status="{{name}}">
			<span class="count">{{count}}</span>
			<div class="hide selecting_menu">
				<ul class="nav nav-tabs nav-stacked">
					<li><a href="javascript:;" status="{{name}}" class="selector_btn select_all">Select All ({{count}})</a></li>
					<li><a href="javascript:;" status="{{name}}" class="selector_btn select_display">Select Visible (<span>0</span>)</a></li>
					<li><a href="javascript:;" status="{{name}}" class="selector_btn select_none">Select None</a></li>
					<li><a href="javascript:;" status="{{name}}" class="selector_btn select_flagged">Select Flagged</a></li>
				</ul>
			</div>
		</span>
		<h5 class="ellipsis" style="width:60%">{{display_name}}</h5>
		<div class="buttons"><a href="javascript:;" class="primarycontextmenu" status="{{name}}"><i class="icon-chevron-down no-border"></i></a></div>
		
	</div>
	<div class="widget-content nopadding">
		<div class='selected_status hide'></div>
		<ul class="sortable ro_list" connect_to="{{connectTo}}" status="{{name}}">
			{{#items}}
			<li id="{{id}}" data-toggle="context" data-target="#context-menu-{{status}}" class="status_{{status}} ro_item {{#has_flag}}flagged{{/has_flag}}" status="{{status}}">
			<div class="ro_item_header">
				<div class="ro_title"><a ro_id="{{id}}" class="tip" tip="<b>{{title}}</b> - {{key}}">{{title}}</a></div>
				<img class="class_icon" tip="{{class}}" src="<?php echo asset_url('img/{{class}}.png', 'base');?>"/>
				<div class="right_content">
					{{#has_flag}}
						<span class="tag no-border-tag flag" tip="Flagged"><i class="icon icon-flag"></i></span>
					{{/has_flag}}
					{{#has_gold}}
						<span class="tag gold_status_flag" tip="<h5>Gold Standard</h5><p>The following record has been verified<br/> as an exemplary record <br/>by the ANDS Metadata Assessment Group.</p>"><i class="icon icon-star"></i></span>
					{{/has_gold}}
					{{#quality_level}}
						<span class="tag ql_{{quality_level}} tipQA" ro_id='{{id}}'>{{quality_level}}</span>
					{{/quality_level}}
					{{#has_error}}
						<a href="javascript:;" class="btn btn-mini btn-danger tipError" ro_id="{{id}}"><i class="icon-white icon-exclamation-sign"></i></a>
					{{/has_error}}
				</div>
			</div>
			<div class="ro_content ">
				<div class="toolbar">
					<div class="btn-group">
						<button class="btn btn-small op" action="view" tip="View" ro_id="{{id}}"><i class="icon icon-search"></i></button>
						{{#editable}}
							<button class="btn btn-small op" action="edit" tip="Edit" ro_id="{{id}}"><i class="icon icon-edit"></i></button>
						{{/editable}}
						{{#advance}}
							<button class="btn btn-small op" action="advance_status" to="{{connectTo}}" tip="Advance Status" ro_id="{{id}}"><i class="icon icon-share-alt"></i></button>
						{{/advance}}
						{{^noMoreOptions}}
							<button class="contextmenu btn btn-small" status="{{name}}" tip="More Actions">More</button>
						{{/noMoreOptions}}
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
			<div class='clearfix'></div>
			</li>
			{{/items}}
			{{#noResult}}<li class="no_records"><small><i>There are no records which match this status</i></small></li>{{/noResult}}
		</ul>
		{{#hasMore}}<span class="show_more" offset="{{offset}}" ds_id="{{ds_id}}" status="{{name}}"><small>Show More</small></span>{{/hasMore}}
	</div>
</div>
</script>


<script type="text/x-mustache" id="mmr_data_more">
{{#items}}
<li id="{{id}}" data-toggle="context" data-target="#context-menu-{{status}}" class="status_{{status}} ro_item {{#has_flag}}flagged{{/has_flag}}" status="{{status}}">
	<div class="ro_item_header">
		<div class="ro_title"><a ro_id="{{id}}" class="tip" tip="<b>{{title}}</b> - {{key}}">{{title}}</a></div>
		<img class="class_icon" tip="{{class}}" src="<?php echo asset_url('img/{{class}}.png', 'base');?>"/>
		<div class="right_content">
			{{#has_flag}}
				<span class="tag no-border-tag flag" tip="Flagged"><i class="icon icon-flag"></i></span>
			{{/has_flag}}
			{{#has_gold}}
				<span class="tag gold_status_flag" tip="<h5>Gold Standard</h5><p>The following record has been verified<br/> as an exemplary record <br/>by the ANDS Metadata Assessment Group.</p>"><i class="icon icon-star"></i></span>
			{{/has_gold}}
			{{#quality_level}}
				<span class="tag ql_{{quality_level}} tipQA" ro_id='{{id}}'>{{quality_level}}</span>
			{{/quality_level}}
			{{#has_error}}
				<a href="javascript:;" class="btn btn-mini btn-danger tipError" ro_id="{{id}}"><i class="icon-white icon-exclamation-sign"></i></a>
			{{/has_error}}
		</div>
	</div>
	<div class="ro_content ">
		<div class="toolbar">
			<div class="btn-group">
				<button class="btn btn-small op" action="view" tip="View" ro_id="{{id}}"><i class="icon icon-search"></i></button>
				{{#editable}}
					<button class="btn btn-small op" action="edit" tip="Edit" ro_id="{{id}}"><i class="icon icon-edit"></i></button>
				{{/editable}}
				{{#advance}}
					<button class="btn btn-small op" action="advance_status" to="{{connectTo}}" tip="Advance Status" ro_id="{{id}}"><i class="icon icon-share-alt"></i></button>
				{{/advance}}
				{{^noMoreOptions}}
					<button class="contextmenu btn btn-small" status="{{status}}" tip="More Actions">More</button>
				{{/noMoreOptions}}
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
	<div class='clearfix'></div>
</li>	
{{/items}}
</script>
<?php $this->load->view('footer');?>