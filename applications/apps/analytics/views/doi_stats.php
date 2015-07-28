<div ng-controller="statCtrl as vm">
    <div class="widget-box" >
        <div class="widget-title">
            <h5>All Time. DOI Statistics</h5>
        </div>
        <div class="widget-container">
            <chart filters="vm.filters" type="'pie'" ctype="'doi'"></chart>
        </div>
    </div>

    <div class="widget-box">
        <div class="widget-title">
            <h5>All Time. DOI Link Checker</h5>
        </div>
        <div class="widget-container">
            <chart filters="vm.filters" type="'pie'" ctype="'doi_client'"></chart>
        </div>
    </div>

    <div class="widget-box">
        <div class="widget-title">
            <h5>All Time. Doi Minted Statistics</h5>
        </div>
        <div class="widget-container">
        {{ vm.nodata }}
            <span ng-if="nodata">There is no data!</span>
            <chart filters="vm.filters" type="'pie'" ctype="'doi_minted'"></chart>
        </div>
    </div>

    <div class="widget-box">
        <div class="widget-title">
            <h5>All Time. Cited Statistics (Thomson Reuters)</h5>
        </div>
        <div class="widget-container">
            <chart filters="vm.filters" type="'pie'" ctype="'tr'"></chart>
        </div>
    </div>

</div>


