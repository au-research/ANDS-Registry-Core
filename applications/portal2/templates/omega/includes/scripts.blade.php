<script>
	var base_url = "{{base_url()}}";
	var registry_url = "{{registry_url()}}";
</script>
@if(isset($lib))
	@foreach($lib as $l)
		@if($l=='angular13')
			<script src="{{asset_url('js/lib/angular.min.js', 'core')}}"></script>
			<script src="{{asset_url('js/lib/angular-route.min.js', 'core')}}"></script>
			<script src="{{asset_url('js/lib/angular-sanitize.min.js', 'core')}}"></script>
		@endif
	@endforeach
@endif
<script src="{{asset_url('omega/js/packages.min.js','templates')}}"></script>
<script src="{{asset_url('omega/js/theme.min.js','templates')}}"></script>

@if(isset($scripts))
	@foreach($scripts as $script)
		<script src="{{asset_url('js/'.$script.'.js')}}"></script>
	@endforeach
@endif