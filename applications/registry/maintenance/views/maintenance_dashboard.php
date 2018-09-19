<?php $this->load->view('header'); ?>
<div ng-app="status_app">
	<div ng-view></div>
</div>

<div id="index_template" class="hide">
	<div class="content-header">
		<h1>Maintenance</h1>
	</div>
	<div id="breadcrumb" style="clear:both;">
		<?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
		<a href="#/" class="current">Maintenance</a>
	</div>
	
	<div class="container-fluid">
		<div class="row-fluid" ng-show="status">
			<div class="span4">
                <div class="widget-box">
                    <div class="widget-title">
                        <h5>Status</h5>
                    </div>
                    <div class="widget-content">
                        <div ng-repeat="module in modules">
                            <span ng-show="status[module].running" class="icon icon-ok"></span>
                            <span ng-show="!status[module].running" class="icon icon-ok"></span>
                            <span ng-bind="module"></span>
                        </div>
                    </div>
                </div>

                <div class="widget-box">
                    <div class="widget-title">
                        <h5>Neo4j Status</h5>
                    </div>
                    <div class="widget-content" ng-show="status.neo4j.running">
                        <dl class="dl dl-horizontal">
                            <dt>Relationship</dt>
                            <dd>{{ status.neo4j.counts.relationships | number }}</dd>
                            <dt>Node</dt>
                            <dd>{{ status.neo4j.counts.nodes | number }}</dd>
                        </dl>
                    </div>
                    <div class="widget-content" ng-show="!status.neo4j.running">
                        <span>{{ status.neo4j.reason }}</span>
                    </div>
                </div>
			</div>
			<div class="span4">
				<div class="widget-box">
					<div class="widget-title">
						<h5>Harvester Status</h5>
					</div>
					<div class="widget-content">
						<dl class="dl dl-horizontal" ng-if="status.harvester.running">
                            <dt>Uptime</dt>
                            <dd>{{ status.harvester.uptime | number }} seconds</dd>

                            <dt>Running Since</dt>
                            <dd>{{ status.harvester.running_since }}</dd>

                            <dt>Harvests Queued</dt>
                            <dd>{{ status.harvester.harvests.queued }}</dd>

                            <dt>Harvests Running</dt>
                            <dd>{{ status.harvester.harvests.running }}</dd>

                            <dt>Harvests Started</dt>
                            <dd>{{ status.harvester.harvests.started }}</dd>

                            <dt>Harvests Stopped</dt>
                            <dd>{{ status.harvester.harvests.stopped }}</dd>

                            <dt>Harvests Errored</dt>
                            <dd>{{ status.harvester.harvests.errored }}</dd>
						</dl>
					</div>
					<div class="widget-content">
						<?php echo anchor('maintenance/harvester', 'Harvester Dashboard', array('class'=>'btn btn-primary')); ?>
					</div>
				</div>
			</div>
            <div class="span4">
                <div class="widget-box">
                    <div class="widget-title">
                        <h5>Task Manager Status</h5>
                    </div>
                    <div class="widget-content">
                        <dl class="dl dl-horizontal" ng-if="status.taskmanager.running">
                            <dt>Uptime</dt>
                            <dd>{{ status.taskmanager.uptime | number }} seconds</dd>

                            <dt>Running Since</dt>
                            <dd>{{ status.taskmanager.running_since }}</dd>

                            <dt>Tasks Queued</dt>
                            <dd>{{ status.taskmanager.counts.queued }}</dd>

                            <dt>Tasks Running</dt>
                            <dd>{{ status.taskmanager.counts.running }}</dd>

                            <dt>Tasks Started</dt>
                            <dd>{{ status.taskmanager.counts.started }}</dd>

                            <dt>Tasks Completed</dt>
                            <dd>{{ status.taskmanager.counts.completed }}</dd>

                            <dt>Tasks Stopped</dt>
                            <dd>{{ status.taskmanager.counts.stopped }}</dd>

                            <dt>Tasks Errored</dt>
                            <dd>{{ status.taskmanager.counts.errored }}</dd>
                        </dl>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>

<?php $this->load->view('footer'); ?>