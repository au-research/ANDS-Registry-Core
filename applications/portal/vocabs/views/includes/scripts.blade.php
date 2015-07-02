<script>
	var base_url = "{{base_url()}}";
	var registry_url = "{{registry_url()}}";
</script>

@if(is_dev())
    <script src="{{asset_url('lib/angular/angular.min.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-route/angular-route.min.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-sanitize/angular-sanitize.min.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-animate/angular-animate.min.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-ui-utils/ui-utils.min.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-bootstrap/ui-bootstrap.min.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-bootstrap/ui-bootstrap-tpls.min.js', 'core')}}"></script>
    <script src="{{asset_url('lib/angular-loading-bar/build/loading-bar.min.js', 'core')}}"></script>
    <script src="{{asset_url('omega/js/packages.min.js','templates')}}"></script>

    <script type="text/javascript" src="{{asset_url('lib/ng-file-upload/angular-file-upload-all.min.js','core')}}"></script>
@else
    <script src="{{ asset_url('js/lib.min.js','core') }}"></script>
@endif

<script type="text/javascript" src="{{ asset_url('js/vocabs_app.js') }}"></script>
<script type="text/javascript" src="{{ asset_url('js/filters.js') }}"></script>
<script type="text/javascript" src="{{ asset_url('js/directives.js') }}"></script>
<script type="text/javascript" src="{{ asset_url('js/vocabs_factory.js') }}"></script>
<script type="text/javascript" src="{{ asset_url('js/vocabs_search_controller.js') }}"></script>
<script type="text/javascript" src="{{ asset_url('js/vocabs_visualise_directive.js') }}"></script>
<script src='//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.js'></script>
<script src="{{asset_url('lib/qtip2/jquery.qtip.js', 'core')}}"></script>
<script type="text/javascript" src="//researchdata.ands.org.au/apps/assets/vocab_widget/js/vocab_widget.js"></script>


@if(isset($scripts))
    @foreach($scripts as $script)
        <script src="{{asset_url('js/'.$script.'.js')}}"></script>
    @endforeach
@endif

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