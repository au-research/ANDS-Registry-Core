@if(count($related) > 0)
    <div class="panel panel-primary element-no-top element-short-bottom panel-content">

        <div class="swatch-white" style="position:relative;">
            <div id="visualisation-overlay">
            </div>
            <div id="visualisation-notice">Click to explore visualisation graph</div>
            <div id="graph-viz"></div>
            <a href="" id="toggle-visualisation"><i class="fa fa-sort"></i></a>
        </div>

        <div class="panel-body swatch-white" style="padding-top:0;">

            {{--Related Publications--}}
            @if (isset($related['publications']) && sizeof($related['publications']['docs']) > 0)
                @include('registry_object/contents/related-publications')
            @endif

            @if (isset($related['data']) && sizeof($related['data']['docs']) > 0)
                @include('registry_object/contents/related-data')
            @endif

            @if (isset($related['organisations']) && sizeof($related['organisations']['docs']) > 0)
                @include('registry_object/contents/related-organisation')
            @endif

            @if (isset($related['researchers']) && sizeof($related['researchers']['docs']) > 0)
                @include('registry_object/contents/related-researchers')
            @endif

            @if (isset($related['programs']) && sizeof($related['programs']['docs']) > 0)
                @include('registry_object/contents/related-program')
            @endif

            @if (isset($related['grants_projects']) && sizeof($related['grants_projects']['docs']) > 0)
                @include('registry_object/contents/related-grants_projects')
            @endif

            @if (isset($related['services']) && sizeof($related['services']['docs']) > 0)
                @include('registry_object/contents/related-service')
            @endif

            @if (isset($related['websites']) && sizeof($related['websites']['docs']) > 0)
                @include('registry_object/contents/related-website')
            @endif

        </div>
    </div>

    <script type="text/javascript">
        function init() {
            var roID = $('#ro_id')[0].value;
//            console.log(base_url + 'registry_object/graph/' + roID)
            window.neo4jd3 = new Neo4jd3('#graph-viz', {
                icons: {
                    'collection': 'folder-open',
                    'activity' : 'flask',
                    'party' : 'user',
                    'service': 'wrench',
                    'publication': 'book',
                    'website': 'globe'
                },
                minCollision: 60,
                neo4jDataUrl: api_url + 'registry/records/' + roID + '/graph',
                nodeRadius: 25,
                zoomFit: false,
                infoPanel: false,
                showCount: true,
                highlight: [{
                    'class': 'RegistryObject',
                    'property':'roId',
                    'value': roID
                }],
                onNodeClick: function(node) {
//                    var url = base_url + 'registry_object/graph/' + node.properties.roId;
//                    $.getJSON(url, function(data) {
//                        var graph = neo4jd3.neo4jDataToD3Data(data);
//                        neo4jd3.updateWithD3Data(graph);
//                    });
                },
                onNodeDoubleClick: function(node) {
                    var url = api_url + 'registry/records/' + node.properties.roId + '/graph';
                    node.loading = true;
                    $.getJSON(url, function(data) {
                        var graph = neo4jd3.neo4jDataToD3Data(data);
                        neo4jd3.updateWithD3Data(graph);
                        node.loading = false;
                    });
                },
                onRelationshipDoubleClick: function(relationship) {
                    console.log('double click on relationship: ' + JSON.stringify(relationship));
                }
            });
        };

        window.onload = init;
    </script>
@endif