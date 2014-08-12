<?php $this->load->view('header'); ?>
<div ng-app="config_app">
	<div ng-view></div>
</div>

<div id="index_template" class="hide">
	<div class="content-header">
		<h1>Configuration</h1>
	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
		<?php echo anchor(registry_url('maintenance'), 'Maintenance'); ?>
		<a href="#/" class="current">Configuration</a>
	</div>
	
	<div class="container-fluid">
		<div class="widget-box">
			<div class="widget-title">
				<h5>Configuration</h5>
			</div>
			<div class="widget-content nopadding">
				<form action="" class="form form-horizontal">

					<fieldset>
						<div ng-repeat="(key, c) in config">
							<div class="control-group" ng-show="c.type=='string'">
								<label class="control-label">{{key | config_readable}}</label>
								<div class="controls">
									<input type="text" class="input-xxlarge" name="" placeholder="{{key | config_readable}} value" ng-model="c.value">
									<span class="help-inline" ng-show="c.gb_value">Overwritten to: {{c.gb_value}}</span>
								</div>
							</div>
						</div>
					</fieldset>

				</form>
				<div class="form-actions">
					<a href="" class="btn btn-primary" ng-click="save()">Save</a>
					<div class="alert alert-{{response.status}}" style="margin-top:10px" ng-show="response">
						{{response.message}}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('footer'); ?>