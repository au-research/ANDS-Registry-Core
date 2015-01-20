<?php
	$order = array('fullCitation');
?>

@if($ro->citations)
<button class="citation">Cite</button>
<div id="citation" class="hide">

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

<button class="export">Export</button>
<div id="export" class="hide">
    @foreach($ro->citations as $citation)
    <p>{{$citation['endNote']}}</p>
    @endforeach
</div>

@endif