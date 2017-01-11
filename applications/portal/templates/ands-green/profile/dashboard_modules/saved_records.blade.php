<h2>Saved Records</h2>
@if(isset($user->user_data['saved_record']))
	<ul>
	@foreach($user->user_data['saved_record'] as $ss)
		<li><a href="{{$ss['url']}}">{{isset($ss['title']) ? $ss['title']: 'No Title'}}</a></li>
	@endforeach
	</ul>
@else
	<p>There's no saved records</p>
@endif