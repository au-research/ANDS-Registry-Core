
<head>
    <meta charset="utf-8">
    <title>{{ isset($ro->core['title']) ? $ro->core['title']: 'Research Data Australia'}}</title>

    <meta name="referrer" content="always">

    <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,200,200italic,300,300italic,400italic,600,600italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <meta property="og:type" content="article"/>

    @if(isset($ro))
        @if(isset($ro->core['title']))
        <meta property="og:title" content="{{$ro->core['title']}}"/>
        @endif
        @if(isset($logo) && $logo !== false)
            <meta property="og:image" content="{{$logo}}"/>
        @else
            <meta property="og:image" content="{{\ANDS\Util\config::get('app.default_base_url')}}assets/core/images/ANDS_logo.JPG"/>
        @endif
        @if(isset($ro->core['description']))
            <?php
                $description = is_array($ro->core['description']) ? implode(" ", $ro->core['description']) : $ro->core['description'];
                $clean_description = str_replace(array('"','[[',']]'), '', $description);
            ?>
            <meta ng-non-bindable property="og:description" content="{{ $clean_description }}"/>
        @else
            <meta property="og:description" content="Find, access, and re-use data for research - from over one hundred Australian research organisations, government agencies, and cultural institutions."/>
        @endif
        @if(isset($ro->core['url']) && \ANDS\Util\config::get('app.default_base_url') == 'https://researchdata.ands.org.au/')
            <meta property="og:url" content="{{$ro->core['url']}}"/>
        @else
            <meta property="og:url" content="{{\ANDS\Util\config::get('app.default_base_url')}}{{$ro->core['slug']}}/{{$ro->core['id']}}"/>
        @endif
        @if(isset($ro->core['site_name']))
            <meta property="og:site_name" content="{{$ro->core['site_name']}}"/>
        @endif
    @else
        <meta property="og:title" content="Research Data Australia"/>

        <meta property="og:image" content="{{\ANDS\Util\config::get('app.default_base_url')}}assets/core/images/ANDS_logo.JPG"/>

        <meta property="og:description" content="Find, access, and re-use data for research - from over one hundred Australian research organisations, government agencies, and cultural institutions."/>

        <meta property="og:site_name" content="Research Data Australia"/>

    @endif
    @include('includes/scripts')
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="yes" name="apple-mobile-web-app-capable">
    @include('includes/styles')

</head>

<!-- Environment Indicator -->
@if(ENVIRONMENT!='production')
	<div class="environment" style="background:{{\ANDS\Util\config::get('app.environment_colour')}};color:white;padding:5px 10px;">
		<h3>{{\ANDS\Util\config::get('app.environment_name')}} - {{ENVIRONMENT}}</h3>
	</div>
@endif