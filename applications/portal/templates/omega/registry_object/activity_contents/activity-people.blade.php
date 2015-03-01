<?php $researcherfound = false; ?>
@if($ro->relationships)
        @if(isset($ro->relationships['party_one']))
            <strong>Researchers </strong>
            <?php
            $peoplecount = 0;
            $researcherfound = true; ?>
            @foreach($ro->relationships['party_one'] as $col)
                <?php
                $peoplecount++;
                $type = readable($col['relation_type']); ?>
                <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" style="margin-right:5px;">{{$col['title']}}</a>({{$type}})
                <?php if($peoplecount<count($ro->relationships['party_one'])) { echo ", ";} ?>
            @endforeach
        @endif
@endif



