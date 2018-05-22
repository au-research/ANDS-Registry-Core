<?php
    $hasRelated = false;
    foreach ($related as $rr) {
        if ($rr != $related['researchers'] && sizeof($rr['docs']) > 0) $hasRelated = true;
    }
?>
@if($hasRelated)
    <div class="panel panel-primary element-no-top element-short-bottom panel-content">
        <div class="panel-body swatch-white" >
            <div id="graph-viz" style="height:450px;"></div>
        </div>
    </div>

    <script type="text/javascript">
        function init() {
            var roID = $('#ro_id')[0].value;
//            console.log(base_url + 'registry_object/graph/' + roID)
            var neo4jd3 = new Neo4jd3('#graph-viz', {
                icons: {
                    'collection': 'folder',
                    'activity' : 'gear',
                    'party' : 'user',
                    'service': 'flask',
                    'cluster': 'cubes'
                },
                minCollision: 70,
                neo4jDataUrl: base_url + 'registry_object/graph/' + roID,
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
                    var url = base_url + 'registry_object/graph/' + node.properties.roId;
                    $.getJSON(url, function(data) {
                        var graph = neo4jd3.neo4jDataToD3Data(data);
                        neo4jd3.updateWithD3Data(graph);
                    });
                },
                onRelationshipDoubleClick: function(relationship) {
                    console.log('double click on relationship: ' + JSON.stringify(relationship));
                }
            });
        };

        window.onload = init;
    </script>

    <div class="panel panel-primary element-no-top element-short-bottom panel-content">
        <div class="panel-body swatch-white">
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
@endif