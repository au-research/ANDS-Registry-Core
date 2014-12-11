<!-- Modal -->
<div class="modal fade bs-example-modal-lg" id="advanced_search" role="dialog" aria-labelledby="Advanced Search" aria-hidden="true" style="z-index:999999">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Advanced Search</h4>
      </div>
      <div class="modal-body">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-4">
              <ul class="nav nav-pills nav-stacked">
                <li ng-repeat="field in advanced_search.fields" ng-class="{'active':field.active==true}"><a href="" ng-click="selectAdvancedField(field)">[[field.display]]</a></li>
              </ul>
            </div>
            <div class="col-md-8">Content</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary">Search</button>
      </div>
    </div>
  </div>
</div>