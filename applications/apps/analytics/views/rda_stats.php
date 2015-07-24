<div class="widget-box" ng-controller="mainCtrl as vm">
    <div class="widget-title">
        <h5>Analytics</h5>
    </div>
    <div class="widget-content">
        <div class="row-fluid">
            <canvas
                ng-if="vm.chartData"
                class="chart chart-line" data="vm.chartData.data"
                labels="vm.chartData.labels" legend="false" series="vm.chartData.series"
                click="vm.onClick" chart-type="vm.chartType">
            </canvas>
            <div ng-if="!vm.chartData && !vm.loading">No Data!</div>
            <div ng-if="!vm.chartData && vm.loading">Loading Please Wait</div>
        </div>
    </div>
</div>