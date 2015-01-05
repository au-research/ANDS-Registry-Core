@if($ro->reuse && isset($ro->reuse[0]['title']))
<h2>Data Reuse Information</h2>
	@foreach($ro->reuse as $reuse_info)
    <p>
        {{$reuse_info['title']}}<br />
        {{$reuse_info['identifier']['identifier_type']}} :
        @if($reuse_info['identifier']['identifier_href'])
        <a href="{{$reuse_info['identifier']['identifier_href']}}">{{$reuse_info['identifier']['identifier_value']}}</a><br />
        @else
        {{$reuse_info['identifier']['identifier_value']}}<br />
        @endif
        {{$reuse_info['notes']}}
    </p>
	@endforeach
@endif

