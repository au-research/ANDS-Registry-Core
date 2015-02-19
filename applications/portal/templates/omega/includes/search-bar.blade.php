<div class="col-md-2">
  <div class="pull-right">
  @if($this->input->get('refer_q'))
    <a href="{{base_url('search')}}#!/{{$this->input->get('refer_q')}}"><i class="fa fa-arrow-left"></i> Return to search</a>
  @endif
  </div>
</div>
<div class="col-md-8" data-os-animation="fadeInDown" data-os-animation-delay="">
    <!-- <form role="search" method="get" action="{{base_url('search')}}"> -->
    <form role="search" method="get" ng-submit="hashChange()">
        <span ng-if="filters.class && filters.class!='collection'">Search is restricted to [[filters.class]]</span>
        <div class="input-group">
            <span class="input-group-btn">
              <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">[[search_type | filter_name]] <span class="caret"></span></button>
              <ul class="dropdown-menu" role="menu">
                <li><a href="" ng-click="search_type='q'">All</a></li>
                <li><a href="" ng-click="search_type='title'">Title</a></li>
                <li><a href="" ng-click="search_type='identifier'">Identifier</a></li>
                <li><a href="" ng-click="search_type='related_people'">Related People</a></li>
                <li><a href="" ng-click="search_type='related_organisations'">Related Organisations</a></li>
                <li><a href="" ng-click="search_type='description'">Description</a></li>
                <!-- <li><a href="" ng-click="search_type='subject'">Subject</a></li> -->
              </ul>
            </span>
            <input type="text" value="" name="q" class="form-control" placeholder="Search for data" ng-model="query">
            <span class="input-group-btn">
              <button class="btn btn-primary" type="button" ng-if="hasFilter()" ng-click="clearSearch()">
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
        
        <div class="pull-right">
            <a href="" ng-click="advanced()">Advanced Search</a>
            <a href="">Map Search</a>
        </div>
    </form>
</div>