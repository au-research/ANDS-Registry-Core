<div class="panel panel-primary swatch-white">
    <div class="panel-body">
    	@if($theme!='standard')
        	<a href="" class="btn btn-lg btn-primary btn-block">Go to Data</a>
        @endif
        @foreach ($aside as $side)
            @include('registry_object/contents/'.$side)
        @endforeach
    </div>
</div>