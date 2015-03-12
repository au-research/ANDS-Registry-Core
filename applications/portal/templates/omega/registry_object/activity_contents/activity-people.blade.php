<?php $researcherfound = false; ?>
@if($ro->relationships)
    @if(isset($ro->relationships['party_one']))
        <strong>Researchers </strong>
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
                $type = readable($col['relation_type']);
            ?>
            @if($col['title'])
                <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" style="margin-right:5px;">{{$col['title']}}</a>({{$type}})
            @endif
            <?php if($peoplecount<count($people)) { echo ", ";} ?>
        @endforeach
    @endif
@endif