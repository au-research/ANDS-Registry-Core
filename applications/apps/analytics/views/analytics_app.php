<?php $this->load->view('header'); ?>
    <div class="content-header">
        <h1>Analytics</h1>
    </div>
    <div id="breadcrumb" style="clear:both;">
        <?php echo anchor(registry_url('auth/dashboard'), '<i class="icon-home"></i> Home'); ?>
        <a href="#/" class="current">Analytics</a>
    </div>
    <div class="container-fluid" ng-app="analytic_app" ng-controller="mainCtrl as vm">
        <div class="row-fluid">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title">
                        <h5>Analytics</h5>
                    </div>
                    <div class="widget-content">
                        <input date-range-picker class="form-control date-picker" type="text" ng-model="vm.filters.period" />
                        <select name="" id="" ng-model="vm.chartType" ng-options="type for type in vm.types"></select>
                        <button class="btn btn-primary" ng-click="vm.getData()">Go</button>
                        <span ng-if="!vm.chartData.data || vm.chartData.length == 0 || vm.loading">
                            Loading... Please wait...
                        </span>
                        <hr/>
                        <canvas
                            ng-if="vm.chartData"
                            class="chart chart-line" data="vm.chartData.data"
                            labels="vm.chartData.labels" legend="false" series="vm.chartData.series"
                            click="vm.onClick" chart-type="vm.chartType">
                        </canvas>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $this->load->view('footer'); ?>