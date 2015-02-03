@if($ro->relatedInfo)
<?php $heading = "<h2>Data Reuse Information</h2>"; ?>
	@foreach($ro->relatedInfo as $relatedInfo)
        @if($relatedInfo['type']=='reuseInformation')
        <?php echo $heading; $heading=''; ?>
        <p>
            {{$relatedInfo['title']}}<br />
            {{$relatedInfo['identifier']['identifier_type']}} :
            @if($relatedInfo['identifier']['identifier_href'])
            <a href="{{$relatedInfo['identifier']['identifier_href']}}">{{$relatedInfo['identifier']['identifier_value']}}</a><br />
            @else
            {{$relatedInfo['identifier']['identifier_value']}}<br />
            @endif
            {{$relatedInfo['notes']}}
        </p>
        @endif
	@endforeach
@endif

