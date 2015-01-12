@if($ro->directsaccess)
    <h3>Data Access</h3>
@elseif($ro->contacts)
    <h3>Data Access</h3>
@elseif($ro->rights)
    <h3>Data Access</h3>
@endif

@if($ro->directaccess)
<div id="access">
    <h2>Direct Download</h2>
	@foreach($ro->directaccess as $access)
    <button class="download"><a href="{{$access['contact_value']}}" target="_blank">{{$access['title']}}</a></button>

 	@endforeach
</div>
@endif