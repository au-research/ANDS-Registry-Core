<div class="panel panel-primary panel-content swatch-white" ng-controller="tagController">
	<div class="panel-heading">User Contributed Tags</div>
	<div class="panel-body">
		@if($ro->tags)
			@foreach($ro->tags as $tag)
				<span for="" class="label label-default tag-{{$tag['type']}}"><i class='fa fa-tag' style="color:white"></i> {{$tag['name']}}</span>
			@endforeach
		@endif
		@if(!$this->user->isLoggedIn())
			<p class="element-short-top"><a href="{{portal_url('profile')}}">Login</a> to tag this record with meaningful keywords to make it easier to discover</p>
		@else
			<form class="element-short-top input-group" style="width:270px" ng-submit="addTag()">
				<input type="text" class="form-control col-md-4" placeholder="Start typing to add tags" ng-model="newTag">
				<span class="input-group-btn">
					<input type="submit" class="btn btn-primary" value="Add Tag"><i class="fa fa-tag"></i> Add Tag</input>
				</span>
			</form>
		@endif
	</div>
</div>