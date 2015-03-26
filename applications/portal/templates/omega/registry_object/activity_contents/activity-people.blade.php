<?php $researcherfound = false; ?>
@if($ro->relationships)
    @if(isset($ro->relationships['party_one']))

        <?php
            $peoplecount = 0;
            $researcherfound = true;
        ?>
        <?php
            //post process, making sure everyone has a title, a slug and a registry object id
            $people = array();
            foreach($ro->relationships['party_one'] as $col) {
                if($col['title'] && $col['slug'] && $col['registry_object_id']) {
                    array_push($people, $col);
                }
            }
        ?>
        @foreach($people as $col)
            <?php
                $peoplecount++;
                $type = readable($col['relation_type'],$col['origin'],$ro->core['class']);
            ?>
            @if($col['title'])
                <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" style="margin-right:5px;">{{$col['title']}}</a>({{$type}})
            @endif
            <?php if($peoplecount<count($people)) { echo ", ";} ?>
        @endforeach
    @endif
@endif

@if($ro->relatedInfo)
    @foreach($ro->relatedInfo as $relatedInfo)
        @if($relatedInfo['type']=='party')
            <?php if($researcherfound){ echo ",  ";} ?>
            @if(isset($relatedInfo['identifier']['identifier_href']['href']))
                <a href="{{$relatedInfo['identifier']['identifier_href']['href']}}">{{$relatedInfo['title']}}</a>
            @else
                {{$relatedInfo['title']}}
            @endif
            @if(isset($relatedInfo['relation']['relation_type']))
                ({{readable($relatedInfo['relation']['relation_type'],'EXPLICIT',$ro->core['class'])}})
            @endif
        @endif
    @endforeach
@endif