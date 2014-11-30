<?php $this->load->view('header'); ?>
<div ng-app="test_suite_app">
	<div ng-view></div>
</div>

<div id="index_template" class="hide">
	<div class="content-header">
		<h1>Test Suite</h1>
	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
		<a href="#/" class="current">Test Suite</a>
	</div>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span3 well">
				<ul class="nav nav-list">
					<li class="nav-header">Tests</li>
					<li ng-repeat="test in tests" ng-class="{'true': 'active'}[currentTest == test]"><a href="" ng-click="do_test(test)">{{test.name}}</a></li>
				</ul>
			</div>
			<div class="span9">
				<div class="widget-box">
					<div class="widget-title">
						<h5>Tests</h5>
					</div>

					<div class="widget-content">
						<div class="alert alert-info" ng-hide="currentTest.result">
							Select a test to run
						</div>

						<div ng-show="currentTest.result">
							<dl class="dl-horizontal">
								<dt>Status</dt>
								<dd>
									<span class="label" ng-class="{'Passed': 'label-success', 'Failed':'label-important'}[currentTest.result.status]">{{currentTest.result.status}}</span>
								</dd>
								<dt>Time taken</dt>
								<dd>{{currentTest.result.elapsed}}</dd>
								<dt>Peak Memory Usage</dt>
								<dd>{{currentTest.result.memory_usage | bytes}}</dd>
							</dl>
							<hr/>
							<div>
								<span bind-html-unsafe="currentTest.result.report"></span>
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>


	</div>
</div>
<?php $this->load->view('footer'); ?>