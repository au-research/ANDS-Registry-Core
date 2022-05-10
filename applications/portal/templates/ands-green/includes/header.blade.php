
<head>
    <meta charset="utf-8">
    <title>{{ isset($ro->core['title']) ? $ro->core['title']: 'Research Data Australia'}}</title>

    <meta name="referrer" content="always">

    <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,200,200italic,300,300italic,400italic,600,600italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Spectral' rel='stylesheet' type='text/css'>
    @if(isset($ro->altmetrics[0]) && $ro->core['class'] == 'collection')
        @foreach($ro->altmetrics as $metric)
        <meta property="{{$metric['type']}}" content="{{$metric['value']}}"/>
        @endforeach
    @endif
    <meta property="og:type" content="article"/>
    @if(isset($ro))
        @if(isset($ro->core['title']))
        <meta property="og:title" content="{{$ro->core['title']}}"/>
        @endif
        @if(isset($logo) && $logo !== false)
            <meta property="og:image" content="{{$logo}}"/>
        @else
            <meta property="og:image" content="{{\ANDS\Util\config::get('app.default_base_url')}}assets/img/ARDC_Research_Data_RGB_FA_Reverse_sml.png"/>
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
        @if(isset($ro->core['url']) && \ANDS\Util\config::get('app.default_base_url') == 'https://researchdata.edu.au/')
            <meta property="og:url" content="{{$ro->core['url']}}"/>
        @else
            <meta property="og:url" content="{{\ANDS\Util\config::get('app.default_base_url')}}{{$ro->core['slug']}}/{{$ro->core['id']}}"/>
        @endif
        @if(isset($ro->core['site_name']))
            <meta property="og:site_name" content="{{$ro->core['site_name']}}"/>
        @endif
    @else
        <meta property="og:title" content="Research Data Australia"/>

        <meta property="og:image" content="{{\ANDS\Util\config::get('app.default_base_url')}}assets/img/ARDC_Research_Data_RGB_FA_Reverse_sml.png"/>

        <meta property="og:description" content="Find, access, and re-use data for research - from over one hundred Australian research organisations, government agencies, and cultural institutions."/>

        <meta property="og:site_name" content="Research Data Australia"/>

    @endif
    @include('includes/scripts')
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="yes" name="apple-mobile-web-app-capable">
    @include('includes/styles')
    <link href="https://unpkg.com/survey-jquery@1.8.29/modern.css" type="text/css" rel="stylesheet" />
    <script src="https://unpkg.com/survey-jquery@1.8.29/survey.jquery.min.js"></script>
</head>
<!-- Environment Indicator -->

@if(ENVIRONMENT!='production' || (isset($ro->core['status']) && $ro->core['status']!='PRODUCTION'))
	<div class="environment" style="background:{{\ANDS\Util\config::get('app.environment_colour')}};color:white;padding:5px 10px;">
        @if(isset($ro->core['status']) && $ro->core['status']!='PRODUCTION')
            <h3>The current record page is for preview only, certain actions are not available under current mode.</h3>
        @else
		    <h3>{{\ANDS\Util\config::get('app.environment_name')}} - {{ENVIRONMENT}} ...</h3>
        @endif
	</div>
@endif