@if($ro->directaccess)
	@foreach($ro->directaccess as $access)
<?php if($access['byteSize']!=''){
    $access['title'] = $access['title'].'  ('.$access['byteSize'].')' ;
}
?>
       <a class="btn btn-info btn-icon-right btn-block element-no-bottom element-no-top" href="{{$access['contact_value']}}"><span>{{$access['mediaType']}}</span>{{$access['title']}} </a>
 	@endforeach

@endif