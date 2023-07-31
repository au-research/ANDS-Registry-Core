<div class="panel-body swatch-white" ng-controller="tagController">
           <h4>User Contributed Tags &nbsp;&nbsp;&nbsp;<a href="#" tip="User tags are terms added to records by Research Data Australia users to assist discovery of these records by themselves and others. By clicking on an added tag you can discover other related records with the same tag. In order to tag a record you must first login to Research Data Australia. Tags can be any string you choose but should be meaningful and have relevance to the record the tag is being added to. To assist you in assigning a tag, previously used tags and terms from the ANZSRC Fields of research (FOR) and Socio-economic objective (SEO) vocabularies are offered via autocomplete suggestions."><i class="fa fa-info"></i></a></h4>
	@if($ro->tags)
		@foreach($ro->tags as $tag)
            @if($ro->core['class'] != 'collection')
                <a href="{{base_url('search')}}#!/tag={{$tag['name']}}/class={{$ro->core['class']}}" class="btn btn-primary btn-link btn-sm btn-icon-left"><span><i class="fa fa-tag"></i></span>{{$tag['name']}}</a>
            @else
			    <a href="{{base_url('search')}}#!/tag={{$tag['name']}}" class="btn btn-primary btn-link btn-sm btn-icon-left"><span><i class="fa fa-tag"></i></span>{{$tag['name']}}</a>
		    @endif
        @endforeach
	@endif
	@if(!$this->user->isLoggedIn())
		<p class="element-short-top"><a href="{{portal_url('profile')}}">Login</a> to tag this record with meaningful keywords to make it easier to discover</p>
	@else
		<form class="element-short-top input-group" style="width:270px" ng-submit="addTag()">
			<input type="text" class="form-control col-md-4" placeholder="Start typing to add tags" ng-model="newTag" typeahead="value.name for value in getSuggestTag($viewValue)" typeahead-min-length="2" typeahead-loading="loadingSuggestions">
			<span class="input-group-btn">
				<input type="submit" class="swatch-white btn btn-primary" value="Add Tag"><i class="fa fa-tag"></i> Add Tag</input>
			</span>
		</form> <br /><em><span id="tag_error"/></em>

		<i ng-show="loadingSuggestions" class="fa fa-refresh fa-spin"></i>
	@endif
</div>