<script>
	var base_url = "{{base_url()}}";
	var registry_url = "{{registry_url()}}";
</script>

<!-- Include the angularJS library since every page will needs it for the search script -->
<script src="{{asset_url('lib/angular/angular.min.js', 'core')}}"></script>
<script src="{{asset_url('lib/angular-route/angular-route.min.js', 'core')}}"></script>
<script src="{{asset_url('lib/angular-sanitize/angular-sanitize.min.js', 'core')}}"></script>

<script src="{{asset_url('omega/js/packages.min.js','templates')}}"></script>
<!-- <script src="{{asset_url('omega/js/theme.js','templates')}}"></script> -->

@if(isset($lib))
	@foreach($lib as $l)
		@if($l=='jquery-ui')
			<script src="{{asset_url('lib/jquery-ui/jquery-ui.js', 'core')}}"></script>
		@elseif($l=='dynatree')
			<script src="{{asset_url('lib/dynatree/src/jquery.dynatree.js', 'core')}}"></script>
        @elseif($l=='qtip')
            <script src="{{asset_url('lib/qtip2/jquery.qtip.js', 'core')}}"></script>
        @endif
	@endforeach
@endif

<!-- Map Search Scripts -->
<script src="{{asset_url('lib/lodash/dist/lodash.min.js', 'core')}}"></script>
<script src="{{asset_url('lib/angular-google-maps/dist/angular-google-maps.min.js', 'core')}}"></script>

<!-- Search Script and Resources is included in every page -->
<script src="{{asset_url('registry_object/js/search_components.js', 'full_base_path')}}"></script>
<script src="{{asset_url('profile/js/profile_components.js', 'full_base_path')}}"></script>
<script src="{{asset_url('registry_object/js/search.js', 'full_base_path')}}"></script>

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