<div class="swatch-white">
	<h1>{{$ro->core['title']}}</h1>
	@include('registry_object/contents/related-objects-list')
	<a href="{{portal_url($ro->core['slug'].'/'.$ro->core['id'])}}" class="btn btn-primary btn-block">View Record</a>
</div>
