<?php
	$order = array('fullCitation');
?>

@if($ro->citations)
<div id="citation">
    <h3>Cite</h3>
    @foreach($order as $o)
	    @foreach($ro->citations as $citation)
            @if($citation['type']==$o)
                <h5>Full Citation:</h5>
	            <p>{{$citation['value']}}</p>
            @endif
	    @endforeach
    @endforeach

    @foreach($ro->citations as $citation)
        @if(!in_array($citation['type'], $order))
            <h5>Citation (Metadata):</h5>
            <p>
            {{$citation['contributors']}}
            ({{$citation['date']}}): {{$citation['title']}}.
            {{$citation['publisher']}}.
            {{$citation['identifier_type']}} :{{$citation['identifier']}}
             <br /><a href="{{$citation['identifierResolved']['href']}}">{{$citation['identifier']}}</a>
            @if($citation['url'])
                <br /><a href="{{$citation['url']}}">{{$citation['url']}}</a>
            @endif
            </p>
        @endif
    @endforeach
</div>
@endif