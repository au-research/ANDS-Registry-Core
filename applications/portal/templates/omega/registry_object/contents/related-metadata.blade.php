<?php
	$has_metadata = false;
	if ($ro->relatedInfo) {
		foreach ($ro->relatedInfo as $relatedInfo) {
			if ($relatedInfo['type']=='metadata') {
				$has_metadata = true;
			}
		}
	}
	
?>
@if($has_metadata)
<div class="swatch-white">
	<div class="panel panel-primary element-no-top element-short-bottom panel-content">
		<div class="panel-heading">
	        <a href="">Other Information</a>
	    </div>
		<div class="panel-body swatch-white">
			@foreach($ro->relatedInfo as $relatedInfo)
				@if($relatedInfo['type']=='metadata')
				    <h5><a href="" class="ro_preview" identifier_doi="{{$relatedInfo['identifier']['identifier_value']}}"><img src="<?php echo base_url()?>assets/core/images/icons/publications.png" style="margin-top: -2px; height: 24px; width: 24px;"> {{$relatedInfo['title']}}</a></h5>
				    <p>
				        <b>{{$relatedInfo['identifier']['identifier_type']}}</b> : 
				        @if($relatedInfo['identifier']['identifier_href'])
				            <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}">{{$relatedInfo['identifier']['identifier_value']}}</a><br />
				        @else
				            {{$relatedInfo['identifier']['identifier_value']}}
				        @endif
				    </p>
				    @if($relatedInfo['relation']['url'])
				        <p>URI : <a href="{{$relatedInfo['relation']['url']}}">{{$relatedInfo['relation']['url']}}</a></p>
				    @endif
			    @endif
			@endforeach
		</div>
	</div>
</div>
@endif