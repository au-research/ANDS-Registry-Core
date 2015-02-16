<!-- Modal -->
<div class="modal advanced-search-modal fade" id="advanced_search" role="dialog" aria-labelledby="Advanced Search" aria-hidden="true" style="z-index:999999">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Advanced Search</h4>
      </div>
      <div class="modal-body">
        <div class="container">
          <div class="row">
            <div class="col-md-2">
              <ul class="nav nav-pills nav-stacked">
                <li ng-repeat="field in advanced_fields" ng-class="{'active':field.active==true}">
                  <a href="" ng-click="selectAdvancedField(field.name)">
                    <span class="badge" ng-show="sizeofField(field.name) > 0">[[sizeofField(field.name)]]</span>
                    [[field.display]]
                  </a>
                </li>
              </ul>
            </div>
            <div class="col-md-10">

              <div ng-show="isAdvancedSearchActive('terms')">
                Terms
              </div>

              <div ng-if="isAdvancedSearchActive(name)" ng-repeat="(name,facet) in allfacets">
                  <ul class="list-unstyled">
                      <li ng-repeat="item in facet">
                          <input type="checkbox" ng-checked="isFacet(name, item.name)" ng-click="toggleFilter(name, item.name, false)">
                          <a href="">[[item.name]] <small>[[item.value]]</small></a>    
                      </li>
                  </ul>
              </div>

              <div ng-if="isAdvancedSearchActive('spatial')">
                @include('registry_object/facet/map')
              </div>

              <div ng-if="isAdvancedSearchActive('class')">
                Search is restricted to: <b>[[filters.class]]</b>
                <ul class="list-unstyled">
                  <li ng-repeat="c in class_choices">
                    <input type="radio" ng-model="filters.class" ng-value="c.name" /> [[c.val]]
                  </li>
                </ul>
              </div>

            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" ng-click="hashChange();closeAdvanced();">Search</button>
      </div>
    </div>
  </div>
</div>