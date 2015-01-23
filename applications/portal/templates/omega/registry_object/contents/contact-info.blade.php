<h3>Contact Information</h3>
@if($ro->contacts)
<div id="contact">
    <h3>Contact Information</h3>
	@foreach($ro->contacts as $contact)
  	    <p>{{$contact['value']}}</p>
 	@endforeach
</div>
@endif