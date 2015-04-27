
<head>
    <meta charset="utf-8">
    <title>{{ isset($ro->core['title']) ? $ro->core['title']: 'Research Data Australia'}}</title>
    @if(isset($ro->core['title']))
        <meta property="og:title" content="{{$ro->core['title']}}"/>
    @endif
        <meta property="og:type" content="article"/>
    @if(isset($logo) && $logo !== false)
        <meta property="og:image" content="{{$logo}}"/>
    @else
        <meta property="og:image" content="{{get_config_item('default_base_url')}}assets/core/images/ANDS_logo.JPG"/>
    @endif
    @if(isset($ro->core['description']))
        <meta property="og:description" content="{{$ro->core['description']}}"/>
    @endif
    @if(isset($ro->core['url']) && get_config_item('default_base_url') == 'https://researchdata.ands.org.au/')
        <meta property="og:url" content="{{$ro->core['url']}}"/>
    @else
        <meta property="og:url" content="{{get_config_item('default_base_url')}}{{$ro->core['slug']}}/{{$ro->core['id']}}"/>
    @endif
    @if(isset($ro->core['site_name']))
        <meta property="og:site_name" content="{{$ro->core['site_name']}}"/>
    @endif

    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="yes" name="apple-mobile-web-app-capable">
    @include('includes/styles')
</head>

<!-- Environment Indicator -->
@if(ENVIRONMENT!='production')
	<div class="environment" style="background:{{get_config_item('environment_colour')}};color:white;padding:5px 10px;">
		<h3>{{get_config_item('environment_name')}} - {{ENVIRONMENT}}</h3>
	</div>
@endif