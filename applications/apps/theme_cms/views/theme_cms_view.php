<?php $this->load->view('header'); ?>
<div class="content-header">
	<h1>Theme CMS</h1>
	<div class="btn-group">
		<a class="btn btn-large" data-toggle="modal" href="#add_blob_modal"><i class="icon icon-plus"></i> Add Blob</a>
		<a class="btn btn-large"><i class="icon icon-plus"></i> Preview</a>
		<a class="btn btn-large"><i class="icon icon-plus"></i> Save</a>
	</div>
</div>
<div id="breadcrumb" style="clear:both;">
	<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
	<?php echo anchor(registry_url('/theme_cms'), 'Theme CMS', array('class'=>'current')); ?>
</div>

<div class="container-fluid" id="main_content">
	<div class="row-fluid">
		<div class="span8">
			<div class="widget-box">
				<div class="widget-title"><h5>Main Content</h5></div>
				<div class="widget-content region" id="region_left"></div>
			</div>
		</div>
		<div class="span4">
			<div class="widget-box">
				<div class="widget-title"><h5>Side Bar</h5></div>
				<div class="widget-content region" id="region_right"></div>
			</div>
		</div>
	</div>
</div>

<div class="modal hide fade" id="add_blob_modal">
	<div class="modal-header">
		<a href="javascript:;" class="close" data-dismiss="modal">×</a>
		<h3>Add a new Blob</h3>
	</div>
	<div class="modal-screen-container">
		<div class="modal-body">
			<div class="alert alert-info">
				Please provide the relevant information
			</div>
			<form action="#" method="get" class="form-horizontal" id="add_blob_form">
				<div class="control-group">
					<label class="control-label">Title</label>
					<div class="controls">
						<input type="title" name="title" value="" placeholder=""/>
					</div>
				</div>
				<textarea class="editor" name="content"></textarea>
			</form>
		</div>
	</div>
	<div class="modal-footer">
		<span id="result"></span>
		<a id="add_confirm" href="javascript:;" class="btn btn-primary">Add</a>
		<a href="#" class="btn hide" data-dismiss="modal">Close</a>
	</div>
</div>

<script type="text/x-mustache" id="blob-template">
{{#.}}
<div class="widget-box">
	<div class="widget-title">
		<span class="icon handle">
			<span><i class="icon icon-resize-vertical"></i></span>
		</span>
		<h5>{{title}}</h5>
		<div class="buttons"><a href="javascript:;" class="blob_menu"><i class="icon-chevron-down no-border"></i></a></div>
	</div>
	<div class="widget-content">{{{content}}}</div>
</div>
{{/.}}
</script>

<?php $this->load->view('footer'); ?>