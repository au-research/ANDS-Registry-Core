@if($ro->subjects)
<?php
$subjectValue = false;
foreach($ro->subjects as $col){
    if($col['subject']!=''){
        $subjectValue=true;
    }
}
?>
    @if($subjectValue)
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
                    @if(isset($col['resolved']) && $col['subject']!='')
                        @if($col['type']=='anzsrc-for' || $col['type']=='anzsrc-seo'|| $col['type']=='iso639-3')
                            <a href="{{base_url().'search/#!/'.$col['type'].'='.$col['subject'].$classSearchComp}}">
                                {{ titleCase($col['resolved']) }}
                            </a> |
                        @elseif($col['type']=='gcmd')
                            <a href="{{base_url().'search/#!/'.$col['type'].'='.$col['resolved'].$classSearchComp}}">{{ $col['subject'] }}</a> |
                        @else
                            <a href="{{base_url().'search/#!/subject_value_resolved='.rawurlencode($col['resolved']).$classSearchComp}}" >{{$col['resolved']}}</a> |
                        @endif
                    @elseif($col['subject']!='')
                    <a href="{{base_url().'search/#!/subject_value='.rawurlencode($col['subject']).$classSearchComp}}">{{$col['subject']}}</a> |
                    @endif
                @endforeach
            </div>
            @if($ro->core['class']!='activity')
                @include('registry_object/contents/tags')
            @endif
        </div>
    </div>
    @endif
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
