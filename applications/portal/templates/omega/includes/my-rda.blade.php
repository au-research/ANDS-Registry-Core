<!-- Modal -->
<div class="modal advanced-search-modal fade" id="my-rda-saved_search-modal" role="dialog" aria-labelledby="My RDA" aria-hidden="true" style="z-index:999999">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" ng-click="save_search('discard')">><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Query Title:</h4>
            </div>
            <div class="modal-body">
                <div class="container">
                    <input type="text" ng-model="query_title"/>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" ng-click="save_search('save')">Save Search</button>
            </div>
        </div>
    </div>
</div>
<div class="modal advanced-search-modal fade" id="my-rda-saved_record-modal" role="dialog" aria-labelledby="My RDA" aria-hidden="true" style="z-index:999999">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" ng-click="save_records('discard')">><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Folder Name:</h4>
            </div>
            <div class="modal-body">
                <div class="container">
                    <input type="text" ng-model="saved_records_folder"/>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" ng-click="save_records('save')">Save Records</button>
            </div>
        </div>
    </div>
</div>