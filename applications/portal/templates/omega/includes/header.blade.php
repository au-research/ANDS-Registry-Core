
<head>
    <meta charset="utf-8">
    <title>{{ isset($ro->core['title']) ? $ro->core['title']: 'Research Data Australia'}}</title>

        <meta property="og:type" content="article"/>
    @if(isset($ro))
        @if(isset($ro->core['title']))
        <meta property="og:title" content="{{$ro->core['title']}}"/>
        @endif
        @if(isset($logo) && $logo !== false)
            <meta property="og:image" content="{{$logo}}"/>
        @else
            <meta property="og:image" content="{{get_config_item('default_base_url')}}assets/core/images/ANDS_logo.JPG"/>
        @endif
        @if(isset($ro->core['description']))
            <?php 
                $clean_description = str_replace(array('"','[[',']]'), '', $ro->core['description']);
            ?>
            <meta ng-non-bindable property="og:description" content="{{ $clean_description }}"/>
        @else
            <meta property="og:description" content="Find, access, and re-use data for research - from over one hundred Australian research organisations, government agencies, and cultural institutions."/>
        @endif
        @if(isset($ro->core['url']) && get_config_item('default_base_url') == 'https://researchdata.ands.org.au/')
            <meta property="og:url" content="{{$ro->core['url']}}"/>
        @else
            <meta property="og:url" content="{{get_config_item('default_base_url')}}{{$ro->core['slug']}}/{{$ro->core['id']}}"/>
        @endif
        @if(isset($ro->core['site_name']))
            <meta property="og:site_name" content="{{$ro->core['site_name']}}"/>
        @endif
    @else
        <meta property="og:title" content="Research Data Australia"/>

        <meta property="og:image" content="{{get_config_item('default_base_url')}}assets/core/images/ANDS_logo.JPG"/>

        <meta property="og:description" content="Find, access, and re-use data for research - from over one hundred Australian research organisations, government agencies, and cultural institutions."/>

        <meta property="og:site_name" content="Research Data Australia"/>

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