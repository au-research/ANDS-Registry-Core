<div ng-controller="statCtrl as vm">
    <div class="widget-box" >
        <div class="widget-title">
            <h5>DOI Statistics</h5>
        </div>
        <div class="widget-container">
            <chart filters="vm.filters" type="'pie'" ctype="'doi'"></chart>
        </div>
    </div>

    <div class="widget-box">
        <div class="widget-title">
            <h5>Cited Statistics (Thomson Reuters)</h5>
        </div>
        <div class="widget-container">
            <chart filters="vm.filters" type="'pie'" ctype="'tr'"></chart>
        </div>
    </div>

</div>


