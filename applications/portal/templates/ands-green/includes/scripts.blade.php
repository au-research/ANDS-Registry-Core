<script>
	var base_url = "{{base_url()}}";
	var registry_url = "{{registry_url()}}";
	var api_url = "{{ api_url()  }}";
	var google_api_key = "{{ \ANDS\Util\Config::get('app.google_api_key') ?: ''  }}"
</script>

@if(\ANDS\Util\config::get('app.tracking'))
    <?php $tracking = \ANDS\Util\config::get('app.tracking'); ?>
    @if(isset($tracking['googleGA']['tagmanager_id']))
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','{{  $tracking['googleGA']['tagmanager_id'] }}');</script>
        <!-- End Google Tag Manager -->
    @endif
    @if($tracking['googleGA']['enabled'])
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $tracking['googleGA']['keys']['id'] }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $tracking['googleGA']['keys']['id'] }}', {
                cookie_domain: '{{ $tracking['googleGA']['keys']['cookie_domain'] }}',
                cookie_flags: 'SameSite=None;Secure',
            });
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
    <script src="{{ dist_url('lib.js') }}"></script>
@else
    <script src="{{ dist_url('lib.js') }}"></script>
@endif

@if(isset($lib))
	@foreach($lib as $l)
		@if($l=='jquery-ui')
			<script type="text/javascript" src="{{asset_url('vendor/jquery-ui/jquery-ui.js', 'core')}}"></script>
        @elseif($l=='textAngular')
        	<link rel='stylesheet' href="{{asset_url('vendor/textAngular/src/textAngular.css', 'core')}}">
            <script src="{{asset_url('vendor/textAngular/dist/textAngular-rangy.min.js', 'core')}}"></script>
            <script src="{{asset_url('vendor/textAngular/dist/textAngular-sanitize.min.js', 'core')}}"></script>
            <script src="{{asset_url('vendor/textAngular/dist/textAngular.min.js', 'core')}}"></script>
        @elseif($l=='colorbox')
            <script src="{{asset_url('vendor/colorbox/jquery.colorbox-min.js', 'core')}}"></script>
        @elseif($l=='mustache')
            <script src="{{asset_url('vendor/mustache/mustache.min.js', 'core')}}"></script>
        @elseif($l=='map')
            <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?{{ \ANDS\Util\Config::get('app.google_api_key') ? 'key='.\ANDS\Util\Config::get('app.google_api_key') : ''  }}&libraries=drawing&amp;sensor=false"></script>
        @elseif($l=='ngupload')
            <script type="text/javascript" src="{{asset_url('vendor/ng-file-upload/angular-file-upload-all.min.js','core')}}"></script>
        @endif
	@endforeach
@endif

@if(is_dev())
    <script src="{{ dist_url('portal_lib.js') }}"></script>
@else
    <script src="{{ dist_url('portal_lib.js') }}"></script>
@endif

<script type="text/javascript" src="https://jira.ardc.edu.au/s/d41d8cd98f00b204e9800998ecf8427e/en_AUc8oc9c-1988229788/6265/77/1.4.7/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector.js?collectorId=d9610dcf"></script>

<script>
    _.contains = _.includes;
</script>
<script src="https://unpkg.com/survey-jquery@1.8.31/survey.jquery.min.js"></script>

@if(isset($scripts))
	@foreach($scripts as $script)
		<script src="{{asset_url('js/'.$script.'.js')}}"></script>
	@endforeach
@endif

<?php if(isset($ro->jsonld[0])){
    print('<script type="application/ld+json">'.$ro->jsonld[0].'</script>');
}
?>