<?php
$order = array('isFundedBy','isManagedBy');
?>
@if($ro->relationships)
@foreach($order as $o)
    @foreach($ro->relationships[0] as $col)
<?php
print("<pre>");
print_r($col);
print("</pre>");
?>
        <a href="<?php echo base_url()?>{{$col['slug']}}/{{$col['registry_object_id']}}" style="margin-right:5px;">{{$col['title']}} <small>({{$col['relation_type']}}) </small></a> 
    @endforeach
@endforeach
@endif
