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
            console.log(base_url + 'registry_object/graph/' + $('#ro_id')[0].value)
            var neo4jd3 = new Neo4jd3('#graph-viz', {
                icons: {
                    'collection': 'folder',
                    'activity' : 'gear',
                    'party' : 'user',
                    'service': 'flask'
                },
                minCollision: 60,
                // neo4jDataUrl: './node_modules/neo4jd3/docs/json/neo4jData.json',
                neo4jDataUrl: base_url + 'registry_object/graph/' + $('#ro_id')[0].value,
                nodeRadius: 25,
                onNodeClick: function(node) {
                    node.fx = node.fy = null;
                    delete node.fixed;
                },
                onNodeDoubleClick: function(node) {
                    node.fixed = true;
                    node.x = 1;
                    node.y = 1;
//                    if (!node.properties.more || node.properties.more < 1) {
//                        return;
//                    }
//                    node.properties.more -= 1;
//                    var x = Math.random();
//                    var y = Math.random();
//                    var neo4jdata = {
//                        "results": [{
//                            "columns": ["user", "entity"],
//                            "data": [{
//                                "graph": {
//                                    "nodes": [{
//                                        "id": x,
//                                        "labels": ["Activity"],
//                                        "properties": {
//                                            "roId": "536332",
//                                            "title": "Some Activity"
//                                        }
//                                    }],
//                                    "relationships": [{
//                                        "id": y,
//                                        "type": "isFundedBy",
//                                        "startNode": x,
//                                        "endNode": node.id,
//                                        "properties": {
//                                            "from": 1473581532586
//                                        }
//                                    }]
//                                }
//                            }]
//                        }],
//                        "errors": []
//                    };
//                    console.log(node.x + "");

                     var maxNodes = 1,
                         data = neo4jd3.randomD3Data(node, maxNodes);
                     neo4jd3.updateWithD3Data(data);

//                    var source = node;
//                    t = d3.zoomTransform(baseSvg.node());
//                    x = -source.y0;
//                    y = -source.x0;
//                    x = x * t.k + viewerWidth / 2;
//                    y = y * t.k + viewerHeight / 2;
//                    d3.select('svg').transition().duration(duration).call( zoomListener.transform, d3.zoomIdentity.translate(x,y).scale(t.k) );
                },
                onRelationshipDoubleClick: function(relationship) {
                    console.log('double click on relationship: ' + JSON.stringify(relationship));
                },
                zoomFit: false,
                infoPanel: true,
                showCount: true
//                roPreview: true
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