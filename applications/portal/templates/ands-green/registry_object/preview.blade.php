<?php

	$search_class = $ro->core['class'];
	if($ro->core['class']=='party') {
		if (strtolower($ro->core['type'])=='person'){
			$search_class = 'party_one';
		} elseif(strtolower($ro->core['type'])=='group') {
			$search_class = 'party_multi';
		}
	}
    $showMore = false;
    if(isset($related['data']) && sizeof($related['data']['contents']) > 0) {
        $showMore = true;
        if(count($related['data']['contents']) == 1) {
            foreach($related['data']['contents'] as $col) {
                if($col && $col['to_identifier'] == $omit) {
                    $showMore = false;
                }
            }
        }
    }

    if (!isset($source)) {
        $source = 'preview';
    }

?>
<div class="swatch-white">
	<h2 class="bordered bold">@include('includes/icon')
      <a href="{{ portal_url($ro->core['slug'].'/'.$ro->core['id'].'?source='.$source) }}">  {{$ro->core['title']}}</a>
    </h2>
	<p>@include('registry_object/contents/the-description')</p>
    @include('registry_object/contents/identifiers-preview')
	@if($ro->core['class']=='party')
		@include('registry_object/contents/contact-info')
	@endif
    @include('registry_object/contents/access-preview')
<!-- ||$ro->identifiers -->
	@if($showMore)
        <h4>More data related to {{$ro->core['title']}}</h4>
        <ul>
            <?php // var_dump($related['data']['docs']) ?>
            @foreach($related['data']['contents'] as $col)
                @if($col && $col['to_identifier'] != $omit)
                    <li><a href="{{$col['to_url']}}" title="{{$col['to_title']}}"  ro_id="{{$col['to_identifier']}}">{{$col['to_title']}}</a></li>
                @endif
            @endforeach
            @if($related['data']['total'] > 5)
                <li><a href="{{ $related['data']['searchUrl'] }}">View all {{ $related['data']['total'] }} related data</a></li>
            @endif
        </ul>
	@endif
    <a href="{{portal_url($ro->core['slug'].'/'.$ro->core['id'].'?source='.$source)}}" class="btn btn-primary btn-link btn-sm pull-right">View Record</a>
</div>