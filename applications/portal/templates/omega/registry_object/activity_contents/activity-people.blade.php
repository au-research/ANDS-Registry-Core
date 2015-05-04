<?php
        $researcherfound = false;
        $sortedArray = Array();
?>
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
                $type = readable($col['relation_type'],$col['origin'],$ro->core['class'],$col['class']);
            ?>
            @if($col['title'])
                <?php
                if(isset($sortedArray[$type])){
                    $sortedArray[$type][]='<a href="<?php echo base_url()?>'.$col['slug'].'/'.$col['registry_object_id'].'" class="ro_preview" ro_id="'.$col['registry_object_id'].'">'.$col['title'].'</a>';
                }else{
                    $sortedArray[$type] = Array();
                    $sortedArray[$type][] = '<a href="<?php echo base_url()?>'.$col['slug'].'/'.$col['registry_object_id'].'" class="ro_preview" ro_id="'.$col['registry_object_id'].'">'.$col['title'].'</a>';
                }
                ?>
            @endif
        @endforeach
    @endif
@endif

@if($ro->relatedInfo)
    @foreach($ro->relatedInfo as $relatedInfo)
        @if($relatedInfo['type']=='party')

            @if(isset($relatedInfo['identifier']['identifier_href']['href']))
                <?php $outstr = '<a href="'.$relatedInfo['identifier']['identifier_href']['href'].'">'.$relatedInfo['title'].' </a>'; ?>
            @else
                <?php $outstr = $relatedInfo['title']; ?>
            @endif
            @if(isset($relatedInfo['relation']['relation_type']))
               <?php  $type = readable($relatedInfo['relation']['relation_type'],'EXPLICIT',$ro->core['class']); ?>
            @else
                <?php  $type = ''; ?>
    @endif

    <?php
    if(isset($sortedArray[$type])){
        $sortedArray[$type][]=$outstr;
    }else{
        $sortedArray[$type] = Array();
        $sortedArray[$type][] = $outstr;
    }
    ?>


        @endif
    @endforeach
@endif

<?php
    if(isset($sortedArray)){
        $typecount = 0;
        foreach($sortedArray as $key=>$value){
            $typecount++;
            $count = 0;
            foreach($value as $researcher){
                echo $researcher;
                $count++;
                if(count($value)>$count) echo ", ";
            }
            if($key!='')
            print(" (".$key.") ");
        /*    if(count($sortedArray)>$typecount){
                echo ' ';
            } */
        }
    }
?>