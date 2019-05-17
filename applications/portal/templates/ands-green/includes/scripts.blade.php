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



@if(is_dev())
    <script src="{{ dist_url('portal_lib.js') }}"></script>
@else
    <script src="{{ dist_url('portal_lib.js') }}"></script>
@endif



<?php if(isset($ro->jsonld[0])){
    print('<script type="application/ld+json">'.$ro->jsonld[0].'</script>');
}
?>