<?php
$order = Array('isFundedBy','isManagedBy','isAdministeredBy')
?>

@if($ro->relationships)
    @foreach($order as $o)

        @if(isset($ro->relationships['party_multi']))
        @foreach($ro->relationships['party_multi'] as $col)
            @if($col['relation_type']==$o)
            <?php  if($o=='isFundedBy'){
                        $output = 'Funded By';
                    } elseif($o=='isManagedBy') {
                        $output = 'Managed by';
                    }else{
                        $output = "Administered by";
                    }
                echo "<strong>".$output." </strong>";?>
                <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" style="margin-right:5px;">{{$col['title']}}</a></br>
            @endif
        @endforeach
        @endif
@endforeach

@endif


