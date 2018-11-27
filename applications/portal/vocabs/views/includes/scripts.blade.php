<script>
	var base_url = "{{base_url()}}";
	var registry_url = "{{registry_url()}}";
@yield('script')
</script>

@if(get_config_item('tracking'))
    <?php $tracking = get_config_item('tracking'); ?>
    @if($tracking['googleGA']['enabled'])
        <script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

          ga('create', '{{ $tracking['googleGA']['keys']['id'] }}', 'auto');
          ga('send', 'pageview');
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
    <script src="{{ asset_url('ands-green/js/packages.min.js','templates') }}"></script>
    <script src="{{ asset_url('js/modified-ui-bootstrap-tpls-0.10.0.js') }}"></script>
@else
    <script src="{{ asset_url('js/lib.js').'?'.getReleaseVersion() }}"></script>
@endif

<script src='//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.js'></script>
<script src="{{asset_url('lib/qtip2/jquery.qtip.js', 'core')}}"></script>
<script type="text/javascript" src="{{ base_url() }}apps/assets/vocab_widget/js/vocab_widget_v2.js"></script>


@if(is_dev())
    <!-- ui-select must be loaded after JQuery. -->
    <script type="text/javascript" src="{{ asset_url('js/lib/ui-select/dist/select.js') }}"></script>
    <script type="text/javascript" src="{{ asset_url('js/vocabs_app.js') }}"></script>
    <script type="text/javascript" src="{{ asset_url('js/filters.js') }}"></script>
    <script type="text/javascript" src="{{ asset_url('js/directives.js') }}"></script>
    <script type="text/javascript" src="{{ asset_url('js/vocabs_factory.js') }}"></script>
    <script type="text/javascript" src="{{ asset_url('js/vocabs_search_controller.js') }}"></script>
    <script type="text/javascript" src="{{ asset_url('js/vocabs_visualise_directive.js') }}"></script>
@else
    <script type="text/javascript" src="{{ asset_url('js/scripts.js').'?'.getReleaseVersion() }}"></script>
@endif



@if(isset($scripts))
    @foreach($scripts as $script)
        <script src="{{asset_url('js/'.$script.'.js').'?'.getReleaseVersion()}}"></script>
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