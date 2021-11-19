@if(true)
    <div class="clearfix"></div>
    <div class="panel panel-primary element-no-top element-short-bottom panel-content">

        <div class="swatch-white" style="position:relative;">
            <div id="visualisation-overlay" class="visualisation-overlay"></div>
            <div id="visualisation-notice" class="visualisation-overlay">Click to explore relationships graph</div>
            <div class="visualisation-toolbar">
                <a href="javascript:;" id="zoom_in" tip="Zoom In" my="left middle" at="right middle"><i class="fa fa-plus"></i></a>
                <a href="javascript:;" id="zoom_out" tip="Zoom Out" my="left middle" at="right middle"><i class="fa fa-minus"></i></a>
                <a href="javascript:;" id="zoom_fit" tip="Fit to window" my="left middle" at="right middle"><i class="fa fa-bullseye"></i></a>
                <a href="javascript:;" id="reset" tip="Reset" my="left middle" at="right middle"><i class="fa fa-refresh"></i></a>
                <a href="javascript:;" title="Help" tip="Help" my="left middle" at="right middle" class="open_rda_help_modal"
                   data-help-tab="graphview" data-toggle="modal" data-target="#help_modal">
                    <i class="fa fa-question-circle"></i> Help
                </a>
            </div>
            <div id="graph-viz"></div>
            <a href="" id="toggle-visualisation"><i class="fa fa-sort"></i></a>
        </div>
        <div class="panel-body swatch-white" style="padding-top:0;">

            {{--Related Publications--}}
            @if (isset($related['publications']['contents']) && sizeof($related['publications']['contents']) > 0)
                @include('registry_object/contents/related-publications')
            @endif

            @if (isset($related['data']['contents']) && sizeof($related['data']['contents']) > 0)
                @include('registry_object/contents/related-data')
            @endif

            @if (isset($related['software']['contents']) && sizeof($related['software']['contents']) > 0)
                @include('registry_object/contents/related-software')
            @endif

            @if (isset($related['organisations']['contents']) && sizeof($related['organisations']['contents']) > 0)
                @include('registry_object/contents/related-organisation')
            @endif

            @if (isset($related['researchers']['contents']) && sizeof($related['researchers']['contents']) > 0)
                @include('registry_object/contents/related-researchers')
            @endif

            @if (isset($related['programs']['contents']) && sizeof($related['programs']['contents']) > 0)
                @include('registry_object/contents/related-program')
            @endif

            @if (isset($related['grants_projects']['contents']) && sizeof($related['grants_projects']['contents']) > 0)
                @include('registry_object/contents/related-grants_projects')
            @endif

            @if (isset($related['services']['contents']) && sizeof($related['services']['contents']) > 0)
                @include('registry_object/contents/related-service')
            @endif

            @if (isset($related['websites']['contents']) && sizeof($related['websites']['contents']) > 0)
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
                onNodeDoubleClick: function(node) {
                    var url = api_url + 'registry/records/' + node.properties.roId + '/graph';
                    neo4jd3.setNodeLoading(node, true);
                    $.getJSON(url, function(data) {
                        var graph = neo4jd3.neo4jDataToD3Data(data);
                        neo4jd3.updateWithD3Data(graph);
                        neo4jd3.setNodeLoading(node, false);
                    });
                },
                onLoading: function() {
                    $('#toggle-visualisation i').removeClass('fa-sort').addClass('fa-spin').addClass('fa-spinner');
                },
                onLoadComplete: function(data) {
                    $('#toggle-visualisation i').removeClass('fa-spin').removeClass('fa-spinner').addClass('fa-sort');
                }
            });
        };

        window.onload = init;
    </script>
@endif