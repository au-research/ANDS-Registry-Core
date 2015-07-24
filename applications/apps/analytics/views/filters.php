<div class="widget-box" ng-controller="filterCtrl as vm">
    <div class="widget-title">
        <h5>Filters</h5>
    </div>
    <div class="widget-content">
        <form class="form-horizontal" ng-submit="vm.getData()">
            <div class="control-group">
                <label for="">Date Range</label>
                <input date-range-picker class="form-control date-picker" type="text" ng-model="vm.filters.period" ng-change="vm.getData()"/>
            </div>
            <div class="control-group">
                <label for="">Group</label>
                <select name="" id="" ng-model="vm.filters.group.value" ng-options="group for group in vm.groups" ng-change="vm.getData()"></select>
            </div>
            <div class="control-group">
                <label for=""></label>
                <div class="controls">
                    <input type="submit" class="btn btn-primary" value="Go">
                </div>
            </div>
        </form>
    </div>
</div>