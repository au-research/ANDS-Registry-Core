@if(is_dev())
    <div class="panel panel-primary panel-content swatch-white">
        <div class="panel-heading">Debug Menu</div>
        <div class="panel-body">
            <ul>
                <li><a href="{{$ro->api_url}}?benchmark=true">API URL</a></li>
                <li><a href="{{$ro->api_url}}sync">Sync Record</a></li>
                <li><a href="{{$ro->api_url}}fixRelationship">Fix Relationship</a></li>
                <li><a href="{{$ro->api_url}}solr_index">View Portal Index</a></li>
                <li><a href="{{$ro->api_url}}relations_index">View Relations Index</a></li>
                <li><a href="{{$ro->api_url}}relationships">View Relationships</a></li>
            </ul>
        </div>
    </div>
@endif