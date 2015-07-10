<script>
	var base_url = "{{base_url()}}";
	var registry_url = "{{registry_url()}}";
</script>

@if(is_dev())
    <script src="{{ asset_url('js/lib/angular/angular.min.js') }} "></script>
    <script src="{{ asset_url('js/lib/angular-route/angular-route.min.js') }}"></script>
    <script src="{{ asset_url('js/lib/angular-sanitize/angular-sanitize.min.js') }}"></script>
    <script src="{{ asset_url('js/lib/angular-animate/angular-animate.min.js') }}"></script>
    <script src="{{ asset_url('js/lib/angular-ui-utils/ui-utils.min.js' )}}"></script>
    <script src="{{ asset_url('js/lib/angular-bootstrap/ui-bootstrap.min.js') }}"></script>
    <script src="{{ asset_url('js/lib/angular-bootstrap/ui-bootstrap-tpls.min.js' )}}"></script>
    <script src="{{ asset_url('js/lib/angular-loading-bar/build/loading-bar.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset_url('js/lib/ng-file-upload/angular-file-upload-all.min.js') }}"></script>
    <script src="{{ asset_url('omega/js/packages.min.js','templates') }}"></script>
@else
    <script src="{{ asset_url('js/lib.js') }}"></script>
@endif

<script src='//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.js'></script>
<script src="{{asset_url('lib/qtip2/jquery.qtip.js', 'core')}}"></script>
<script type="text/javascript" src="//researchdata.ands.org.au/apps/assets/vocab_widget/js/vocab_widget.js"></script>

@if(is_dev())
    <script type="text/javascript" src="{{ asset_url('js/vocabs_app.js') }}"></script>
    <script type="text/javascript" src="{{ asset_url('js/filters.js') }}"></script>
    <script type="text/javascript" src="{{ asset_url('js/directives.js') }}"></script>
    <script type="text/javascript" src="{{ asset_url('js/vocabs_factory.js') }}"></script>
    <script type="text/javascript" src="{{ asset_url('js/vocabs_search_controller.js') }}"></script>
    <script type="text/javascript" src="{{ asset_url('js/vocabs_visualise_directive.js') }}"></script>
@else
    <script type="text/javascript" src="{{ asset_url('js/scripts.js') }}"></script>
@endif



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
    <script src="{{ asset_url('js/lib/less.js/dist/less.min.js') }}"></script>
@endif