@if($ro->subjects)
<div class="swatch-white">
	<div class="panel element-no-top element-short-bottom">
		<div class="panel-heading"> Subjects </div>
		<div class="panel-body swatch-white">
			<?php 
				$subjects = $ro->subjects;
				uasort($subjects, 'subjectSortResolved');
                $classSearchComp = '';
                if($ro->core['class'] != 'collection')
                    $classSearchComp = '/class='.$ro->core['class'];
			?>
			@foreach($subjects as $col)
                @if(isset($col['resolved']))
                    @if($col['type']=='anzsrc-for')
                        <a href="{{base_url().'search/#!/anzsrc-for='.$col['subject'].$classSearchComp}}" itemprop="about keywords">{{$col['resolved']}}</a> |
                    @elseif($col['type']=='anzsrc-seo')
                        <a href="{{base_url().'search/#!/anzsrc-seo='.$col['subject'].$classSearchComp}}" itemprop="about keywords">{{$col['resolved']}}</a> |
                    @else
                        <a href="{{base_url().'search/#!/subject_value_resolved='.rawurlencode($col['resolved']).$classSearchComp}}" itemprop="about keywords">{{$col['resolved']}}</a> |
                    @endif
                @else
                <a href="{{base_url().'search/#!/subject_value='.rawurlencode($col['subject']).$classSearchComp}}" itemprop="about keywords">{{$col['subject']}}</a> |
                @endif
			@endforeach
		</div>
        @if($ro->core['class']!='activity')
            @include('registry_object/contents/tags')
        @endif
	</div>
</div>
@else
    @if($ro->core['class']!='activity')
    <div class="swatch-white">
        <div class="panel panel-primary element-no-top element-short-bottom panel-content">
            <!-- <div class="panel-heading"> <a href="">Tags</a> </div> -->
            @include('registry_object/contents/tags')
        </div>
    </div>
    @endif
@endif
