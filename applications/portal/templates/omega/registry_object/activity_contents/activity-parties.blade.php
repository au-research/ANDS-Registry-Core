
@if($ro->relationships)
     @foreach($ro->relationships['party_multi'] as $col)
            <?php  if($col['relation_type']=='isFundedBy'){
    $output = 'Funded by';
} else {
    $output = 'Managed by';
}
echo "<strong>".$output." </strong>";?>
         <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" style="margin-right:5px;">{{$col['title']}}</a></br>
    @endforeach

@endif


