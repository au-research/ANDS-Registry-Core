@if($ro->directsaccess)
    <h3>Data Access</h3>
@elseif($ro->contacts)
    <h3>Data Access</h3>
@elseif($ro->rights)
    <h3>Data Access</h3>
@endif

@if($ro->directaccess)
<?php
    print_r($ro->directaccess)
?>
<div id="access">
    <h2>Direct Download</h2>
	@foreach($ro->directaccess as $access)
    <button class="download"><a href="" target="_blank">{{$access['value']}}</a></button>

 	@endforeach
</div>
@endif