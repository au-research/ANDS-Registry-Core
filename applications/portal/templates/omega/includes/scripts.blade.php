<script>
	var base_url = "{{base_url()}}";
	var registry_url = "{{registry_url()}}";
</script>

<!-- Include the angularJS library since every page will needs it for the search script -->
<script src="{{asset_url('lib/angular/angular.min.js', 'core')}}"></script>
<script src="{{asset_url('lib/angular-route/angular-route.min.js', 'core')}}"></script>
<script src="{{asset_url('lib/angular-sanitize/angular-sanitize.min.js', 'core')}}"></script>
<script src="{{asset_url('lib/angular-animate/angular-animate.min.js', 'core')}}"></script>
<script src="{{asset_url('lib/angular-ui-utils/ui-utils.min.js', 'core')}}"></script>
<script src="{{asset_url('lib/angular-bootstrap/ui-bootstrap.min.js', 'core')}}"></script>
<script src="{{asset_url('lib/angular-bootstrap/ui-bootstrap-tpls.min.js', 'core')}}"></script>

<!-- Angular Loading Bar -->
<link rel="stylesheet" href="{{asset_url('lib/angular-loading-bar/build/loading-bar.min.css', 'core')}}"></link>
<script src="{{asset_url('lib/angular-loading-bar/build/loading-bar.min.js', 'core')}}"></script>

<!-- Angular Select -->
<link rel="stylesheet" href="{{asset_url('lib/angular-ui-select/dist/select.css', 'core')}}"></link>
<script src="{{asset_url('lib/angular-ui-select/dist/select.js', 'core')}}"></script>


<script src="{{asset_url('omega/js/packages.min.js','templates')}}"></script>
<!-- <script src="{{asset_url('omega/js/theme.js','templates')}}"></script> -->
<script src="{{asset_url('js/scripts.js', 'core')}}"></script>




@if(isset($lib))
	@foreach($lib as $l)
		@if($l=='jquery-ui')
			<script src="{{asset_url('lib/jquery-ui/jquery-ui.js', 'core')}}"></script>
		@elseif($l=='dynatree')
			<script src="{{asset_url('lib/dynatree/src/jquery.dynatree.js', 'core')}}"></script>
        @elseif($l=='qtip')
            <script src="{{asset_url('lib/qtip2/jquery.qtip.js', 'core')}}"></script>
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


<!-- Map Search Scripts -->
<script src="{{asset_url('lib/lodash/dist/lodash.min.js', 'core')}}"></script>
<script src="{{asset_url('lib/angular-google-maps/dist/angular-google-maps.js', 'core')}}"></script>

<!-- Search Script and Resources is included in every page -->


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


<script type="text/javascript" src="https://jira.ands.org.au/s/d41d8cd98f00b204e9800998ecf8427e/en_AUc8oc9c-1988229788/6265/77/1.4.7/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector.js?collectorId=d9610dcf"></script>


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

@if(isset($scripts))
	@foreach($scripts as $script)
		<script src="{{asset_url('js/'.$script.'.js')}}"></script>
	@endforeach
@endif