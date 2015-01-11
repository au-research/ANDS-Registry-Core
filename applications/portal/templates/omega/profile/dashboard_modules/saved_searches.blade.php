<h2>Saved Search</h2>
@if(isset($user->user_data['saved_search']))
	<ul>
	@foreach($user->user_data['saved_search'] as $ss)
		<li><a href="{{portal_url()}}search/#!/{{$ss['query_string']}}">{{$ss['query_string']}}</a></li>
	@endforeach
	</ul>
@else
	<p>There's no saved search available</p>
@endif