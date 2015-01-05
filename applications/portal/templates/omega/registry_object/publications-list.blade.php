@if($ro->publications && isset($ro->publications[0]))
<h2>Related Publications</h2>
<ul>
	@foreach($ro->publications as $pub)

    <img src="<?php echo base_url()?>assets/core/images/icons/publications.png"   style="margin-top: -2px; height: 24px; width: 24px;"> {{$pub['title']}}<br/>
    {{$pub['identifier']['identifier_type']}} :
    @if($pub['identifier']['identifier_href'])
    <a href="{{$pub['identifier']['identifier_href']['href']}}">{{$pub['identifier']['identifier_value']}}</a><br />
    @else
    {{$pub['identifier']['identifier_value']}}<br />
    @endif
    @if($pub['relation']['url'])
    URI : <a href="{{$pub['relation']['url']}}">{{$pub['relation']['url']}}</a><br />
    @endif
	@endforeach
</ul>
@endif

