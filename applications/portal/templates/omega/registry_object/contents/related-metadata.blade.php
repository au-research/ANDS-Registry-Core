<?php
	$has_metadata = false;
    $has_moreInfo = false;
    $notTypes = array('publication','dataQualityInformation','website','reuseInformation','metadata');
	if ($ro->relatedInfo) {
		foreach ($ro->relatedInfo as $relatedInfo) {
			if ($relatedInfo['type']=='metadata') {
				$has_metadata = true;
			}
		}
        foreach ($ro->relatedInfo as $relatedInfo) {
            if (!in_array($relatedInfo['type'],$notTypes)) {
                if($relatedInfo['type'] == 'service' && ($relatedInfo['relation']['url']== '' || $relatedInfo['title'] != '') && !$has_moreInfo)
                {
                    $has_moreInfo = false;
                }
                elseif($relatedInfo['type'] == 'party' && in_array(trim($relatedInfo['identifier']['identifier_value']), $resolvedPartyIdentifiers)  && !$has_moreInfo){
                    $has_moreInfo = false;
                }
                else{
                    $has_moreInfo = true;
                }

            }
        }
	}
	
?>
@if($has_metadata || $has_moreInfo)
<div class="swatch-white">
	<div class="panel">
		<div class="panel-heading">Other Information</div>
		<div class="panel-body swatch-white">
			@foreach($ro->relatedInfo as $relatedInfo)
				@if($relatedInfo['type']=='metadata')
				    <h5><a href="" class="ro_preview" identifier_doi="{{$relatedInfo['identifier']['identifier_value']}}"><img src="<?php echo base_url()?>assets/core/images/icons/publications.png" style="margin-top: -2px; height: 24px; width: 24px;"> {{$relatedInfo['title']}}</a></h5>
				    <p>
				        <b>{{$relatedInfo['identifier']['identifier_type']}}</b> : 
				        @if(isset($relatedInfo['identifier']['identifier_href']['href']))
				            <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}">{{$relatedInfo['identifier']['identifier_value']}}</a>{{$relatedInfo['identifier']['identifier_href']['display_icon']}}<br />
				        @else
				            {{$relatedInfo['identifier']['identifier_value']}}
				        @endif
				    </p>
				    @if($relatedInfo['relation']['url'])
				        <p>URI : <a href="{{$relatedInfo['relation']['url']}}">{{$relatedInfo['relation']['url']}}</a></p>
				    @endif
			    @endif
			@endforeach
            @foreach($ro->relatedInfo as $relatedInfo)

            @if($relatedInfo['type']=='service' && ($relatedInfo['title']!='' || $relatedInfo['relation']['url']==''))
            @elseif($relatedInfo['type']=='service' && $relatedInfo['title']=='' && $relatedInfo['relation']['url']!='')
                <p>
                    <b>{{$relatedInfo['identifier']['identifier_type']}}</b> :
                    @if($relatedInfo['identifier']['identifier_href']['href'])
                    <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}">{{$relatedInfo['identifier']['identifier_value']}}</a>{{$relatedInfo['identifier']['identifier_href']['display_icon']}}<br />
                    @else
                    {{$relatedInfo['identifier']['identifier_value']}}
                    @endif
                </p>

                @if($relatedInfo['relation']['url'])
                <p>URI : <a href="{{$relatedInfo['relation']['url']}}">{{$relatedInfo['relation']['url']}}</a></p>
                @endif
                @if($relatedInfo['notes'])
                <p>{{$relatedInfo['notes']}}</p>
                @endif
            @elseif(!in_array($relatedInfo['type'],$notTypes) && !in_array(trim($relatedInfo['identifier']['identifier_value']), $resolvedPartyIdentifiers))

                <h5> {{$relatedInfo['title']}}</h5>
                <p>
                    <b>{{$relatedInfo['identifier']['identifier_type']}}</b> :
                    @if(isset($relatedInfo['identifier']['identifier_href']['href']))
                    <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}">{{$relatedInfo['identifier']['identifier_value']}}</a>{{ isset($relatedInfo['identifier']['identifier_href']['display_icon']) ? $relatedInfo['identifier']['identifier_href']['display_icon'] : '' }}<br />
                    @else
                    {{$relatedInfo['identifier']['identifier_value']}}
                    @endif
                </p>

                @if($relatedInfo['relation']['url'])
                <p>URI : <a href="{{$relatedInfo['relation']['url']}}">{{$relatedInfo['relation']['url']}}</a></p>
                @endif
                @if($relatedInfo['notes'])
                <p>{{$relatedInfo['notes']}}</p>
                @endif
            @endif
            @endforeach
		</div>
	</div>
</div>
@endif