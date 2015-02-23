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
                          <input type="checkbox" ng-checked="isFacet(facet.name, item.name)" ng-click="togglePreFilter(facet.name, item.name, false)">
                          <a href="" ng-click="togglePreFilter(facet.name, item.name, false)">[[item.name]] <small>[[item.value]]</small></a>    
                      </li>
                  </ul>
              </div>

              <div ng-if="isAdvancedSearchActive('temporal')">
                <label for="">From Year</label>
                <select ng-model="prefilters.year_from" ng-options="year_from as year_from for year_from in temporal_range">
                    <option value="" style="display:none">From Year</option>
                </select>
                <label for="">To Year</label>
                <select ng-model="prefilters.year_to" ng-options="year_to as year_to for year_to in temporal_range | orderBy:year_to:true">
                    <option value="" style="display:none">To Year</option>
                </select>
              </div>

              <div ng-if="isAdvancedSearchActive('subject')">
                <div>
                  <ul class="list-unstyled">
                    <li ng-repeat="item in vocab_tree">
                      <input type="checkbox" ng-checked="isVocabSelected(item, prefilters)" ui-indeterminate="isVocabParentSelected(item)" ng-click="togglePreFilter('anzsrc-for', item.notation, false)">
                      <a href="" ng-click="getSubTree(item)">[[item.prefLabel]]</a>
                      <ul ng-if="item.subtree">
                        <li ng-repeat="item2 in item.subtree">
                          <input type="checkbox" ng-checked="isVocabSelected(item, prefilters2)" ui-indeterminate="isVocabParentSelected(item2)" ng-click="togglePreFilter('anzsrc-for', item2.notation, false)">
                          <a href="" ng-click="getSubTree(item2)">[[item2.prefLabel]]</a>
                          <ul ng-if="item2.subtree">
                            <li ng-repeat="item3 in item2.subtree">
                              <input type="checkbox" ng-checked="isVocabSelected(item, prefilters3)" ui-indeterminate="isVocabParentSelected(item3)" ng-click="togglePreFilter('anzsrc-for', item3.notation, false)">
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

              <div ng-if="isAdvancedSearchActive('review')">
                <div class="panel panel-primary" ng-cloak>
                    <div class="panel-heading">Current Search</div>
                    <!-- <div class="panel-body swatch-white">
                        [[filters]]
                    </div> -->
                    <div class="panel-body swatch-white">
                        <div ng-repeat="(name, value) in prefilters" ng-if="showFilter(name)">
                            <h4>[[name | filter_name]]</h4>
                            <ul class="listy" ng-show="isArray(value) && name!='anzsrc-for'">
                                <li ng-repeat="v in value track by $index"> <a href="" ng-click="togglePreFilter(name, v, true)">[[v]]<small><i class="fa fa-remove"></i></small></a> </li>
                            </ul>
                            <ul class="listy" ng-show="isArray(value)===false && name!='anzsrc-for'">
                                <li> <a href="" ng-click="togglePreFilter(name, value, true)">[[value]]<small><i class="fa fa-remove"></i></small></a> </li>
                            </ul>
                            <div resolve ng-if="name=='anzsrc-for'" subjects="value" vocab="anzsrc-for"></div>
                        </div>
                        <div class="panel-body swatch-white">
                          <a href="" class="btn btn-primary" ng-click="advancedSearch();"><i class="fa fa-search"></i> Search</a>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="well">
                  <b>[[ preresult.response.numFound ]]</b> result(s) found with these filters. Hit <a href="" ng-click="advancedSearch();">Search</a>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" ng-click="advanced('close');">Cancel</button>
        <button type="button" class="btn btn-primary" ng-click="advancedSearch();">Search</button>
      </div>
    </div>
  </div>
</div>