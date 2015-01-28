<div class="col-md-8 col-md-offset-2" data-os-animation="fadeInDown" data-os-animation-delay="">
    <!-- <form role="search" method="get" action="{{base_url('search')}}"> -->
    <form role="search" method="get" ng-submit="hashChange()">
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
            <input type="text" value="" name="q" class="form-control" placeholder="Search Research Data Australia" ng-model="query">
            <span class="input-group-btn">
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
