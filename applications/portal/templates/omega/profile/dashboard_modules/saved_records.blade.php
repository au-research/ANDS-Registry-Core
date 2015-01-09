<h2>Saved Records</h2>
@if(isset($user->user_data['saved_record']))
	<ul>
	@foreach($user->user_data['saved_record'] as $ss)
		<li><a href="{{$ss['url']}}">{{$ss['title']}}</a></li>
	@endforeach
	</ul>
@else
	<p>There's no saved search available</p>
@endif