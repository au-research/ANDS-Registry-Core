<div class="col-md-8 col-md-offset-2" data-os-animation="" data-os-animation-delay="">
    <form role="search" method="get" action="{{base_url('search')}}">
        <div class="input-group">
            <span class="input-group-btn">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">All <span class="caret"></span></button>
                <ul class="dropdown-menu" role="menu">
                  <li><a href="#">Title</a></li>
                  <li><a href="#">Description</a></li>
                  <li><a href="#">Subject</a></li>
                </ul>
            </span>
            <input type="text" value="" name="q" class="form-control" placeholder="Search Research Data Australia" ng-model="filters.q">
            <span class="input-group-btn">
                <button class="btn btn-primary" type="submit" value="Search">
                    <i class="fa fa-search"></i> Search
                </button>
            </span>
        </div>
        <div class="pull-right">
            <a href="">Advanced Search</a>
            <a href="">Map Search</a>
        </div>
    </form>
</div>