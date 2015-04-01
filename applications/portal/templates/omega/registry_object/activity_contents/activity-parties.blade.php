<?php
$order = Array('isFundedBy','isManagedBy','isAdministeredBy');
$prev_out ='';
?>

@if($ro->relationships)
    @foreach($order as $o)

        @if(isset($ro->relationships['party_multi']))
        @foreach($ro->relationships['party_multi'] as $col)
            @if($col['relation_type']==$o)
            <?php  if($o=='isFundedBy'){
                        if($prev_out=='Funded by'){
                            $output='';
                        }
                        else{
                            $output = 'Funded by';
                        }
                        $prev_out = 'Funded by';
                    } elseif($o=='isManagedBy') {
                        if($prev_out=='Managed by'){
                            $output='';
                        }
                        else{
                            $output = 'Managed by';
                        }
                        $prev_out = 'Managed by';

                    }else{
                        if($prev_out=='Administered by'){
                            $output='';
                        }
                        else{
                            $output = 'Administered by';
                        }
                        $prev_out = 'Administered by';
                    }
                if($output==''){
                    echo",";
                }else{
                    echo"<br />";
                }
                echo "<strong>".$output;?>
                <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" style="margin-right:5px;" class="ro_preview" ro_id="{{$col['registry_object_id']}}">{{$col['title']}}</a></strong>
            @endif
        @endforeach
        @endif
@endforeach

@endif


