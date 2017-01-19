<div class="panel panel-primary swatch-white">
    <div class="panel-body">
    	<a href="" class="btn btn-lg btn-primary btn-block">Go to Data</a>
        @foreach ($aside as $side)
            @include('registry_object/contents/'.$side)
        @endforeach
    </div>
</div>