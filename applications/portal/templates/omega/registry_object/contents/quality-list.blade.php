@if($ro->relatedInfo)
<div class="swatch-white">
    <div class="panel panel-primary element-no-top element-short-bottom panel-content">
        <div class="panel-heading">
            <a href="">Data Quality Information</a>
        </div>
        <div class="panel-body swatch-white">
            @foreach($ro->relatedInfo as $relatedInfo)
                @if($relatedInfo['type']=='dataQualityInformation')
                    <p>
                    {{$relatedInfo['title']}}<br />
                    {{$relatedInfo['identifier']['identifier_type']}} :
                    @if(isset($relatedInfo['identifier']['identifier_href']))
                        <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}">{{$relatedInfo['identifier']['identifier_value']}}</a><br />
                    @else
                        {{$relatedInfo['identifier']['identifier_value']}}<br />
                    @endif
                    {{$relatedInfo['notes']}}
                    </p>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endif