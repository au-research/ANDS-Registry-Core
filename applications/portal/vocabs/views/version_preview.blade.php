<div class="modal-body swatch-white">
    <div class="container-fluid">
         <div class="row">
              <div class="col-md-12">



                      <h4>{{ titlecase($version['title']) }}</h4>
                        @if(isset($version['access_points']))
                      @foreach($version['access_points'] as $ap)
                      @if($ap['type']=='file')
                      <a class="btn btn-lg btn-block btn-primary" href="{{ portal_url('vocabs/download/?file='.$ap['uri']) }}"><i class="fa fa-cube"></i> Download File</a>
                      @endif
                      @endforeach
                      @foreach($version['access_points'] as $ap)
                      @if($ap['type']!='file')
                      <div class="btn-group btn-group-justified element element-no-bottom element-no-top" role="group" aria-label="...">
                          <a class="btn btn-sm btn-default" href="{{ $ap['uri'] }}"><i class="fa fa-edit"></i> Access {{ $ap['type'] }} ({{ $ap['format'] }})</a>
                      </div>
                      @endif
                      @endforeach
                        @endif
                      <p class="element-short-top">{{ isset($version['note']) ? $version['note']: '' }}</p>



               </div>
         </div>
    </div>
</div>