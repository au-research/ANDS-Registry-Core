<div class="col-md-2">
  <div class="pull-right">
  @if($this->input->get('refer_q'))
    <a href="{{base_url('search')}}#!/{{$this->input->get('refer_q')}}"><i class="fa fa-arrow-left"></i> Return to search</a>
  @endif
  </div>
</div>
<div class="col-md-8" data-os-animation="fadeInDown" data-os-animation-delay="" ng-cloak>
    <!-- <form role="search" method="get" action="{{base_url('search')}}"> -->
    <form role="search" method="get" ng-submit="newSearch(query)" >
        <span ng-if="filters.class && filters.class!='collection'">
          <a href="" ng-click="changeFilter('class', 'collection', true)">Search is restricted to [[ filters.class | getLabelFor:class_choices ]] <i class="fa fa-remove"></i></a>
        </span>
        <div class="input-group">
            <span class="input-group-btn">
              <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">[[search_type | filter_name]] <span class="caret"></span></button>
              <ul class="dropdown-menu" role="menu">
                <li ng-repeat="item in sf.search_types" ng-if="filters.class!='activity'"><a href="" ng-click="setSearchType(item.value)">[[ item.label ]]</a></li>
                <li ng-repeat="item in sf.search_types_activities" ng-if="filters.class=='activity'"><a href="" ng-click="setSearchType(item.value)">[[ item.label ]]</a></li>
                <!-- <li><a href="" ng-click="search_type='subject'">Subject</a></li> -->
              </ul>
            </span>
            <input type="text" value="" name="q" class="form-control" placeholder="Search for [[ filters.class | getLabelFor:class_choices ]]" ng-model="query">
            <span class="input-group-btn">
              <button class="btn btn-primary" type="button" ng-if="hasFilter() && !isLoading()" ng-click="clearSearch()">
                <i class='fa fa-remove'></i>
              </button>
              <button class="btn btn-primary" type="button" ng-if="isLoading()">
                <i class='fa fa-refresh fa-spin'></i> Loading...
              </button>
              <button class="btn btn-primary" type="submit" value="Search">
                  <i class="fa fa-search"></i> Search
              </button>
            </span>
        </div>
        <div class="pull-left" ng-if="filters.class=='collection'">
          <input type="checkbox" ng-checked="filters.access_rights=='open'" ng-click="toggleAccessRights()"> <a href="" ng-click="toggleAccessRights()" tip="Restrict search to open data only">Open data only</a>
        </div>
        <div class="pull-right">
            <a href="" ng-click="advanced()" style="margin-right:6px">Advanced Search</a>
            <a href="" ng-click="advanced('spatial')" ng-if="filters.class=='collection'">Map Search</a>
        </div>
    </form>
    
</div>