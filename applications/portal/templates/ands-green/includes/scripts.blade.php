<script>
	var base_url = "{{base_url()}}";
	var registry_url = "{{registry_url()}}";
	var api_url = "{{ api_url()  }}";
	var google_api_key = "{{ \ANDS\Util\Config::get('app.google_api_key') ?: ''  }}"
</script>

@if(\ANDS\Util\config::get('app.tracking'))
    <?php $tracking = \ANDS\Util\config::get('app.tracking'); ?>
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

@if(is_dev())
    <script src="{{ dist_url('lib.js') }}"></script>
@else
    <script src="{{ dist_url('lib.js') }}"></script>
@endif

@if(isset($lib))
	@foreach($lib as $l)
		@if($l=='jquery-ui')
			<script type="text/javascript" src="{{asset_url('vendor/jquery-ui/jquery-ui.js', 'core')}}"></script>
		@elseif($l=='dynatree')
			<script type="text/javascript" src="{{asset_url('vendor/dynatree/dist/jquery.dynatree.js', 'core')}}"></script>
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

<script type="text/javascript" src="https://jira.ands.org.au/s/d41d8cd98f00b204e9800998ecf8427e/en_AUc8oc9c-1988229788/6265/77/1.4.7/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector.js?collectorId=d9610dcf"></script>

<script>
    _.contains = _.includes;
</script>

@if(isset($scripts))
	@foreach($scripts as $script)
		<script src="{{asset_url('js/'.$script.'.js')}}"></script>
	@endforeach
@endif

<?php if(isset($ro->jsonld[0])){
    print('<script type="application/ld+json">'.$ro->jsonld[0].'</script>');
}
?>