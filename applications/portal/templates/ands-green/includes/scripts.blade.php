<script>
	var base_url = "{{base_url()}}";
	var registry_url = "{{registry_url()}}";
	var api_url = "{{ api_url()  }}";
</script>

@if(get_config_item('tracking'))
    <?php $tracking = get_config_item('tracking'); ?>
    @if($tracking['googleGA']['enabled'])
        <script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

          ga('create', '{{ $tracking['googleGA']['keys']['id'] }}', 'auto');
          ga('send', 'pageview');
          urchin_id = '{{ $tracking['googleGA']['keys']['id'] }}';
        </script>
    @endif
    @if($tracking['luckyOrange']['enabled'])
        <script type='text/javascript'>
        window.__wtw_lucky_site_id = {{ $tracking['luckyOrange']['keys']['id'] }};

        (function() {
            var wa = document.createElement('script'); wa.type = 'text/javascript'; wa.async = true;
            wa.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://cdn') + '.luckyorange.com/w.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(wa, s);
          })();
        </script>
    @endif

@endif

<!-- Include the angularJS library since every page will needs it for the search script -->
@if(is_dev())
    <script src="{{asset_url('lib/angular/angular.min.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-route/angular-route.min.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-sanitize/angular-sanitize.min.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-animate/angular-animate.min.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-ui-utils/ui-utils.min.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-bootstrap/ui-bootstrap.min.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-bootstrap/ui-bootstrap-tpls.min.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-loading-bar/build/loading-bar.min.js', 'core')}}"></script>

    <script src="{{ asset_url('ands-green/js/d3.min.js', 'templates') }}"></script>
    <script src="{{ asset_url('ands-green/js/neo4jd3/js/neo4jd3.js', 'templates') }}"></script>

    <script src="{{asset_url('ands-green/js/packages.min.js','templates')}}"></script>
    <script src="{{asset_url('lib/qtip2/jquery.qtip.js', 'core')}}"></script>
    <script src="{{asset_url('js/scripts.js', 'core')}}"></script>
@else
    <script src="{{ asset_url('js/lib.min.js','core') }}"></script>
@endif

@if(isset($lib))
	@foreach($lib as $l)
		@if($l=='jquery-ui')
			<script type="text/javascript" src="{{asset_url('lib/jquery-ui/jquery-ui.js', 'core')}}"></script>
		@elseif($l=='dynatree')
			<script type="text/javascript" src="{{asset_url('lib/dynatree/dist/jquery.dynatree.js', 'core')}}"></script>
        @elseif($l=='textAngular')
        	<link rel='stylesheet' href="{{asset_url('lib/textAngular/src/textAngular.css', 'core')}}">
            <script src="{{asset_url('lib/textAngular/dist/textAngular-rangy.min.js', 'core')}}"></script>
            <script src="{{asset_url('lib/textAngular/dist/textAngular-sanitize.min.js', 'core')}}"></script>
            <script src="{{asset_url('lib/textAngular/dist/textAngular.min.js', 'core')}}"></script>
        @elseif($l=='colorbox')
            <script src="{{asset_url('lib/colorbox/jquery.colorbox-min.js', 'core')}}"></script>
        @elseif($l=='mustache')
            <script src="{{asset_url('lib/mustache/mustache.min.js', 'core')}}"></script>
        @elseif($l=='map')
            <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?libraries=drawing&amp;sensor=false"></script>
        @elseif($l=='ngupload')
            <script type="text/javascript" src="{{asset_url('lib/ng-file-upload/angular-file-upload-all.min.js','core')}}"></script>
        @endif
	@endforeach
@endif

@if(is_dev())
    <script src="{{asset_url('lib/lodash/dist/lodash.min.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-google-maps/dist/angular-google-maps.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-lz-string/angular-lz-string.js', 'core')}}"></script>
    <script src="{{asset_url('registry_object/js/record_components.js', 'full_base_path')}}"></script>
    <script src="{{asset_url('profile/js/profile_components.js', 'full_base_path')}}"></script>
    <script src="{{asset_url('registry_object/js/search.js', 'full_base_path')}}"></script>
    <script src="{{asset_url('registry_object/js/portal-filters.js', 'full_base_path')}}"></script>
    <script src="{{asset_url('registry_object/js/query_builder.js', 'full_base_path')}}"></script>
    <script src="{{asset_url('registry_object/js/portal-directives.js', 'full_base_path')}}"></script>
    <script src="{{asset_url('registry_object/js/vocab-factory.js', 'full_base_path')}}"></script>
    <script src="{{asset_url('registry_object/js/search_controller.js', 'full_base_path')}}"></script>
    <script src="{{asset_url('registry_object/js/search-factory.js', 'full_base_path')}}"></script>
@else
    <script src="{{ asset_url('js/portal_lib.js', 'core') }}"></script>
@endif

<script type="text/javascript" src="https://jira.ands.org.au/s/d41d8cd98f00b204e9800998ecf8427e/en_AUc8oc9c-1988229788/6265/77/1.4.7/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector.js?collectorId=d9610dcf"></script>

<!--
    Minh:
    Fix for the lodash module that the angular-google-maps: "~2.0.12" component hasn't update yet.
    Basically lodash >= 4.2.0 renamed the contains function into includes
    this is for backward compatibility
-->
<script>
    _.contains = _.includes;
</script>

@if(is_dev())
    <!-- LESS.JS for development only-->
    <script>
      less = {
        env: "development",
        async: false,
        fileAsync: false,
        poll: 1000,
        logLevel:0
      };
    </script>
    <script src="{{asset_url('lib/less.js/dist/less.min.js', 'core')}}"></script>
@endif

@if(isset($scripts))
	@foreach($scripts as $script)
		<script src="{{asset_url('js/'.$script.'.js')}}"></script>
	@endforeach
@endif

<?php if(isset($ro->jsonld[0])){
    print('<script type="application/ld+json">'.$ro->jsonld[0].'</script>');
}
?>