@if($ro->quality && isset($ro->quality[0]['title']))
<h2>Data Quality Information</h2>
	@foreach($ro->quality as $quality_info)
    <p>
        {{$quality_info['title']}}<br />
        {{$quality_info['identifier']['identifier_type']}} :
        @if($quality_info['identifier']['identifier_href'])
            <a href="{{$quality_info['identifier']['identifier_href']}}">{{$quality_info['identifier']['identifier_value']}}</a><br />
        @else
            {{$quality_info['identifier']['identifier_value']}}<br />
        @endif
        {{$quality_info['notes']}}
     </p>
	@endforeach
@endif

