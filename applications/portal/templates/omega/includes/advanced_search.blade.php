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
                <input type="text" ng-model="query">
                <div ng-controller="QueryBuilderCtrl">
                  <div class="alert alert-info">
                      <strong>Query Construction</strong><br>
                      <span ng-bind-html="output"></span>
                  </div>
                  <query-builder group="filter.group"></query-builder>
                </div>
              </div>

              <div ng-if="isAdvancedSearchActive(facet.name)" ng-repeat="facet in allfacets">
                  <ul class="list-unstyled" ng-if="facet.name!='subject'">
                      <li ng-repeat="item in facet.value">
                          <input type="checkbox" ng-checked="isFacet(facet.name, item.name)" ng-click="toggleFilter(facet.name, item.name, false)">
                          <a href="">[[item.name]] <small>[[item.value]]</small></a>    
                      </li>
                  </ul>
              </div>

              <div ng-if="isAdvancedSearchActive('subject')">
                <div>
                  <ul class="list-unstyled">
                    <li ng-repeat="item in vocab_tree">
                      <input type="checkbox" ng-checked="isVocabSelected(item)" ui-indeterminate="isVocabParentSelected(item)" ng-click="toggleFilter('anzsrc-for', item.notation, false)">
                      <a href="" ng-click="getSubTree(item)">[[item.prefLabel]]</a>
                      <ul ng-if="item.subtree">
                        <li ng-repeat="item2 in item.subtree">
                          <input type="checkbox" ng-checked="isVocabSelected(item2)" ui-indeterminate="isVocabParentSelected(item2)" ng-click="toggleFilter('anzsrc-for', item2.notation, false)">
                          <a href="" ng-click="getSubTree(item2)">[[item2.prefLabel]]</a>
                          <ul ng-if="item2.subtree">
                            <li ng-repeat="item3 in item2.subtree">
                              <input type="checkbox" ng-checked="isVocabSelected(item3)" ui-indeterminate="isVocabParentSelected(item3)" ng-click="toggleFilter('anzsrc-for', item3.notation, false)">
                              <a href="" ng-click="getSubTree(item3)">[[item3.prefLabel]]</a>
                            </li>
                          </ul>
                        </li>
                      </ul>
                    </li>
                  </ul>
                </div>
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