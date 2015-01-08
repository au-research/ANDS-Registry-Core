<script>
	var base_url = "{{base_url()}}";
	var registry_url = "{{registry_url()}}";
</script>

<!-- Include the angularJS library since every page will needs it for the search script -->
<script src="{{asset_url('lib/angular/angular.min.js', 'core')}}"></script>
<script src="{{asset_url('lib/angular-route/angular-route.min.js', 'core')}}"></script>
<script src="{{asset_url('lib/angular-sanitize/angular-sanitize.min.js', 'core')}}"></script>

<script src="{{asset_url('omega/js/packages.min.js','templates')}}"></script>
<script src="{{asset_url('omega/js/theme.min.js','templates')}}"></script>

@if(isset($lib))
	@foreach($lib as $l)
		@if($l=='jquery-ui')
			<script src="{{asset_url('lib/jquery-ui/jquery-ui.js', 'core')}}"></script>
		@elseif($l=='dynatree')
			<script src="{{asset_url('lib/dynatree/src/jquery.dynatree.js', 'core')}}"></script>
		@endif
	@endforeach
@endif

<!-- Search Script and Resources is included in every page -->
<script src="{{asset_url('js/search_components.js', 'core')}}"></script>
<script src="{{asset_url('js/profile_components.js', 'core')}}"></script>
<script src="{{asset_url('js/search.js', 'core')}}"></script>


@if(isset($scripts))
	@foreach($scripts as $script)
		<script src="{{asset_url('js/'.$script.'.js')}}"></script>
	@endforeach
@endif