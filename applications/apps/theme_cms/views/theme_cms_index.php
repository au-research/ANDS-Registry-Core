<?php $this->load->view('header'); ?>
<div ng-app="theme_cms_app">
	<div ng-view></div>
</div>

<div id="list_template" class="hide">
	<div class="content-header">
		<h1>Theme CMS</h1>
		<div class="btn-group">
			<a class="btn btn-large" href="#/new_page"><i class="icon icon-plus"></i> New Page</a>
		</div>
	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
		<a href="#/" class="current">Theme CMS</a>
	</div>
	
	<div class="container-fluid">
		<div class="widget-box">
			<div class="widget-title">
				<h5>Theme Pages</h5>
			</div>
			<div class="widget-content">
				<ul style="list-style-type:none;clear:both;">
					<li ng-repeat="page in pages" style="float:left;margin:10px 20px;">
						<div class="thumbnail" style="max-width:250px">
							<a href="#/view/{{page.slug}}" class="thumbnail">
								<img src="{{page.img_src}}" alt="" ng-show="page.img_src">
								<img src="http://placehold.it/150x150&amp;text=Placeholder" alt="" ng-show="!page.img_src">
							</a>
							<div class="caption">
								<h3><a href="#/view/{{page.slug}}">{{page.title}}</a></h3>
								<p ng-show="page.desc">{{page.desc}}</p>
								<p>
									<a href="#/view/{{page.slug}}" class="btn btn-primary">View</a>
									<span class="label label-success" ng-show="page.visible=='1'">Visible</span>
									<span class="label label-important" ng-show="page.visible=='0'">Not Visible</span>
								</p>
							</div>
						</div>
					</li>
				</ul>
				<div class="clearfix"></div>

				<div ng-show="pages.length == 0" class="alert alert-info">
					There are no existing pages. Create new one with the button bellow
				</div>
				<hr>
				<a class="btn" href="#/new_page"><i class="icon icon-plus"></i> New Page</a>
			</div>
		</div>
	</div>
</div>

<div id="new_page_template" class="hide">
	<div class="content-header">
		<h1>Theme CMS</h1>
	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
		<a href="#/">Theme CMS</a>
		<a href="#/new_page" class="current">New Page</a>
	</div>
	<div class="container-fluid">
		<div class="widget-box">
			<div class="widget-title">
				<h5>Add New Page</h5>
			</div>
			<div class="widget-content">
				<form ng-submit="addPage()" class="form">
					<fieldset>
						<div class="control-group">
							<label for="">Theme Page Title: </label>
							<input type="text" placeholder="Theme Page Title" name="title" ng-model="new_page_title" required>
						</div>
						<span class="help-block" ng-show="new_page_title">A file name {{new_page_title | slugify}}.json will be automatically generated upon creation</span>
						<div class="control-group">
							<label for="">Cover Image: </label>
							<input type="text" placeholder="Cover Image URL" name="img_src" ng-model="new_page_img_src">
						</div>
						<div class="control-group">
							<label for="">Description: </label>
							<textarea name="new_page_desc" id="" cols="30" rows="10" placeholder="Theme Page Description" ng-model="new_page_desc"></textarea>
						</div>
						
						<button type="submit" class="btn btn-primary">Add New Page</button>
					</fieldset>
					<div class="alert alert-success" ng-show="ok">{{ok.msg}} <a href="#/view/{{ok.slug}}">Click here</a> to view your page</div>
					<div class="alert alert-danger" ng-show="fail">{{fail.msg}}</div>
				</form>
			</div>
		</div>
	</div>
</div>

<div id="view_page_template" class="hide">
	<div class="content-header">
		<h1 ng-dblclick="editPageTitle = true" ng-hide="editPageTitle">{{page.title}}</h1>
		<h1 ng-show="editPageTitle">
			<form class="form form-horizontal">
				<input type="text" ng-model="page.title">
				<button class="btn btn-small" ng-click="editPageTitle = false"><i class="icon icon-ok"></i></button>
			</form>
		</h1>
		<div class="btn-group">
			<a class="btn btn-large" ng-click="save()"><i class="icon icon-hdd"></i> Save</a>
			<a class="btn btn-large" ng-click="config=true"><i class="icon icon-wrench"></i> Config</a>
			<a class="btn btn-large" href="<?php echo portal_url('theme/{{page.slug}}'); ?>" target="_blank"><i class="icon icon-eye-open"></i> Preview</a>
			<a class="btn btn-large btn-danger" tip="Delete" ng-click="deleting('true')"><i class="icon-white icon-trash"></i></a>
		</div>
	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
		<a href="#/">Theme CMS</a>
		<a href="#/view/{{page.slug}}" class="current">{{page.title}}</a>
		<div class='pull-right muted' style="margin:10px 30px 0px 0px;"><small>{{saved_msg}}</small></div>
	</div>
	
	<div class="container-fluid">

		
		<div class="widget-box" ng-show="config">
			<div class="widget-title">
				<h5>{{page.title}} - Configuration</h5>
			</div>
			<div class="widget-content">
				<div class="alert alert-info">
					<p>Changing the Theme Page Title will not change the page slug: <b>{{page.slug}}</b></p>
				</div>
				<form class="form form-horizontal" ng-submit="">
					<fieldset>
						<div class="control-group">
							<label for="">Theme Page Title: </label>
							<input type="text" placeholder="Theme Page Title" name="title" ng-model="page.title" required>
						</div>
						<div class="control-group">
							<label for="">Cover Image: </label>
							<input type="text" placeholder="Cover Image URL" name="img_src" ng-model="page.img_src">
						</div>
						<div class="control-group">
							<label for="">Description: </label>
							<textarea cols="30" rows="10" placeholder="Theme Page Description" ng-model="page.desc"></textarea>
						</div>
						<div class="control-group">
							<label for="">Visible: </label>
							<select ng-model="page.visible">
								<option value="1">Visible</option>
								<option value="0">Not Visible</option>
							</select>
						</div>
						<div class="control-group">
							<label for="">Secret Tag</label>
							<input type="text" placeholder="Secret Tag" ng-model="page.secret_tag">
							<button class="btn btn-small" ng-click="generateSecretTag()">Generate</button>
						</div>
						<a href="" ng-click="config=false" class="btn">Close</a>
					</fieldset>
				</form>
			</div>
		</div>

		<div class="row-fluid" ng-show="show_delete_confirm">
			<div class="span3">&nbsp;</div>
			<div class="span6">
				<div class="widget-box">
					<div class="widget-title">
						<h5>Confirmation</h5>
					</div>
					<div class="widget-content">
						<div class="alert alert-danger">
							<p>Are you sure you want to delete this page? This action is irreversible</p>
							<a href="" class="btn btn-danger" ng-click="delete(page.slug)">Yes, Delete the page</a>
							<a href="" class="btn btn-link" ng-click="deleting('false')">Close</a>
						</div>
					</div>
				</div>
			</div>
			<div class="span3">&nbsp;</div>
		</div>

		<div class="row-fluid">
			<div class="span8">
				<?php 
					$data = array(
						'title'=>'Main Content',
						'region'=>'left'
					);
					$this->load->view('content', $data);
				?>
			</div>
			<div class="span4">
				<?php 
					$data = array(
						'title'=>'Side Bar',
						'region'=>'right'
					);
					$this->load->view('content', $data);
				?>
			</div>
		</div>
	</div>

</div>

<div id="delete_page_template" class="hide">
	<div class="content-header">
		<h1>{{page.title}}</h1>
	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
		<a href="#/">Theme CMS</a>
		<a href="#/view/{{page.slug}}">{{page.title}}</a>
		<a href="#/new_page" class="current">Delete</a>
	</div>
	
</div>

<?php $this->load->view('footer'); ?>