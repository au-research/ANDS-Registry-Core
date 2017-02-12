<div class="modal-body swatch-white">
    <div class="container-fluid">
         <div class="row">
              <div class="col-md-12">
                      <h4>{{ htmlspecialchars(titlecase($version['title'])) }}</h4>
                        @if(isset($version['version_access_points']))
                      @foreach($version['version_access_points'] as $ap)
                      @if($ap['type']=='file')
                      <a class="btn btn-lg btn-block btn-primary" style="white-space: normal;" href="{{ json_decode($ap['portal_data'])->uri }}"><i class="fa fa-cube"></i> Download File <span class="small">({{ json_decode($ap['portal_data'])->format }})</span></a>
                      @endif
                      @endforeach
                      @foreach($version['version_access_points'] as $ap)
                      @if($ap['type']!='file')
                      <div class="btn-group btn-group-justified element element-no-bottom element-no-top" role="group" aria-label="...">
                          <a class="btn btn-sm btn-default" href="{{ json_decode($ap['portal_data'])->uri }}" target="_blank"><i class="fa fa-edit"></i> Access {{ $ap['type'] }}
                              @if(isset(json_decode($ap['portal_data'])->format))
                                  ({{ json_decode($ap['portal_data'])->format }})
                              @endif
                          </a>
                      </div>
                      @endif
                      @endforeach
                        @endif
                      <p class="element-short-top">{{ isset($version['note']) ? $version['note']: '' }}</p>
               </div>
         </div>
    </div>
</div>